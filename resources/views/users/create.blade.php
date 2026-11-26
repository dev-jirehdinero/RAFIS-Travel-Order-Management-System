@extends('layouts.app')

@section('title', 'New account · RAFIS')

@section('content')
    <div class="mx-auto max-w-lg rounded-lg border bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-xl font-semibold">New account</h1>
        <p class="mb-4 text-sm text-gray-500">
            Set a temporary password and give it to the person. They can change it, and their email,
            under <span class="font-medium">My account</span>.
        </p>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium">Full name</label>
                <input id="name" name="name" value="{{ old('name') }}" required placeholder="DELA CRUZ, JUAN P."
                       class="mt-1 w-full rounded border px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-gray-500">Format: SURNAME, FIRST NAME M.</p>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium">Email (used to log in)</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="off"
                       class="mt-1 w-full rounded border px-3 py-2 text-sm">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium">Temporary password</label>
                <input id="password" name="password" type="text" required minlength="8" autocomplete="off"
                       class="mt-1 w-full rounded border px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-gray-500">At least 8 characters.</p>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium">Role</label>
                <select id="role" name="role" required class="mt-1 w-full rounded border px-3 py-2 text-sm">
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', 'staff') === $role)>{{ \App\Enums\Role::from($role)->label() }}</option>
                    @endforeach
                </select>
            </div>

            <fieldset class="space-y-3 rounded border bg-gray-50 p-3">
                <legend class="px-1 text-sm font-medium">Staff record</legend>
                <p class="text-xs text-gray-500">
                    Travel orders belong to a staff record. Leave this on "New" to create one from the details above
                    (Admin accounts don't need one).
                </p>
                <div>
                    <label for="personnel_id" class="block text-sm font-medium">Link to existing staff record</label>
                    <select id="personnel_id" name="personnel_id" class="mt-1 w-full rounded border bg-white px-3 py-2 text-sm">
                        <option value="">— New staff record —</option>
                        @foreach ($personnelOptions as $option)
                            <option value="{{ $option->id }}" @selected((string) old('personnel_id') === (string) $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="position" class="block text-sm font-medium">Position (for a new staff record)</label>
                    <input id="position" name="position" value="{{ old('position') }}" placeholder="Information Officer II"
                           class="mt-1 w-full rounded border bg-white px-3 py-2 text-sm">
                </div>
            </fieldset>

            <div class="flex items-center gap-3">
                <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Create account</button>
                <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
