<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(): View
    {
        return view('account.edit');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()->id),
            ],
            // Changing the login email needs the password, so a left-open browser can't hijack the account.
            'current_password' => ['required', 'current_password'],
        ]);

        $request->user()->update(['email' => $data['email']]);

        return redirect()->route('account.edit')->with('success', 'Your email was updated. Use it the next time you log in.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ]);

        // The User model's "hashed" cast hashes this automatically.
        $request->user()->update(['password' => $data['password']]);

        return redirect()->route('account.edit')->with('success', 'Your password was updated.');
    }
}
