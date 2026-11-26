@extends('layouts.app')

@section('title', 'Edit account · RAFIS')

@section('content')
    <div class="mx-auto max-w-lg space-y-6">
        <div>
            <h1 class="text-xl font-semibold">Edit account</h1>
            <p class="text-sm text-gray-500">{{ $account->email }}</p>
        </div>

        {{-- Name and role --}}
        <section class="rounded-lg border bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Details</h2>

            <form method="POST" action="{{ route('users.update', $account) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $account->name) }}" required
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-gray-500">Format: SURNAME, FIRST NAME M. The staff record's name changes with it.</p>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium">Role</label>
                    @if ($isSelf)
                        <input type="hidden" name="role" value="{{ $account->role->value }}">
                        <p class="mt-1 rounded border bg-gray-50 px-3 py-2 text-sm text-gray-600">
                            {{ $account->role->label() }} <span class="text-xs">(you can't change your own role)</span>
                        </p>
                    @else
                        <select id="role" name="role" required class="mt-1 w-full rounded border px-3 py-2 text-sm">
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @selected(old('role', $account->role->value) === $role)>{{ \App\Enums\Role::from($role)->label() }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                @if ($account->personnel_id === null)
                    <div>
                        <label for="position" class="block text-sm font-medium">Position</label>
                        <input id="position" name="position" value="{{ old('position') }}" placeholder="Information Officer II"
                               class="mt-1 w-full rounded border px-3 py-2 text-sm">
                        <p class="mt-1 text-xs text-gray-500">Only needed when changing this account to Staff (it has no staff record yet).</p>
                    </div>
                @else
                    <p class="text-xs text-gray-500">Staff record: {{ $account->personnel->name }}</p>
                @endif

                <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Save changes</button>
            </form>
        </section>

        {{-- Reset password --}}
        <section class="rounded-lg border bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold">Reset password</h2>
            <p class="mb-4 text-sm text-gray-500">
                Set a new temporary password and give it to the person. They'll be signed out
                and can change it under <span class="font-medium">My account</span>.
            </p>

            <form method="POST" action="{{ route('users.password', $account) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="password" class="block text-sm font-medium">New temporary password</label>
                    <input id="password" name="password" type="text" required minlength="8" autocomplete="off"
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-gray-500">At least 8 characters.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Reset password</button>
                    <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">Back to accounts</a>
                </div>
            </form>
        </section>
    </div>
@endsection
