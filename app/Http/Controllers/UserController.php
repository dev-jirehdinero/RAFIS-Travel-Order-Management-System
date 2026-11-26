<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index', [
            'users' => User::query()->with('personnel:id,name')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => $request->user()->assignableRoles(),
            // Only staff records that don't have an account yet.
            'personnelOptions' => Personnel::query()->doesntHave('user')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $personnelId = $data['personnel_id'] ?? null;

            // Staff / OIC accounts need a staff record to hold their travel orders.
            if (! $personnelId && $data['role'] !== Role::Admin->value) {
                $personnelId = Personnel::create([
                    'name' => $data['name'],
                    'position' => $data['position'],
                    'employment_status' => 'Permanent',
                ])->id;
            }

            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
                'personnel_id' => $personnelId,
            ]);
        });

        return redirect()
            ->route('users.index')
            ->with('success', "Account created for {$data['email']}. Give them the temporary password and ask them to change it under My account.");
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('update', $user);

        $isSelf = $user->is($request->user());

        return view('users.edit', [
            'account' => $user->load('personnel:id,name'),
            'isSelf' => $isSelf,
            'roles' => $isSelf ? [$user->role->value] : $request->user()->assignableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $user) {
            $personnelId = $user->personnel_id;

            if ($personnelId) {
                // Keep the staff record's name in step with the account name.
                $user->personnel?->update(['name' => $data['name']]);
            } elseif ($data['role'] === Role::Staff->value) {
                $personnelId = Personnel::create([
                    'name' => $data['name'],
                    'position' => $data['position'],
                    'employment_status' => 'Permanent',
                ])->id;
            }

            $user->update([
                'name' => $data['name'],
                'role' => $data['role'],
                'personnel_id' => $personnelId,
            ]);
        });

        return redirect()
            ->route('users.index')
            ->with('success', "{$user->name}'s account was updated.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'password' => ['required', 'string', Password::min(8)],
        ]);

        // forceFill: the "hashed" cast hashes it. A new remember_token ends "remember me" logins.
        $user->forceFill([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ])->save();

        // Sign the person out everywhere (only possible with database sessions, and never for yourself).
        if (! $user->is($request->user()) && config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        return redirect()
            ->route('users.index')
            ->with('success', "Password reset for {$user->name}. Give them the temporary password and ask them to change it under My account.");
    }
}
