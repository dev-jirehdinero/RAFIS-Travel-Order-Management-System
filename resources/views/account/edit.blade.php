@extends('layouts.app')

@section('title', 'My account · RAFIS')

@section('content')
    <div class="mx-auto max-w-md space-y-6">
        <h1 class="text-xl font-semibold">My account</h1>

        {{-- Email --}}
        <section class="rounded-lg border bg-white p-6 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold">Email</h2>
            <p class="mb-4 text-sm text-gray-500">This is the email you use to log in.</p>

            <form method="POST" action="{{ route('account.email.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="email" class="block text-sm font-medium">Email address</label>
                    <input id="email" name="email" type="email" required autocomplete="email"
                           value="{{ old('email', auth()->user()->email) }}"
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="email_current_password" class="block text-sm font-medium">Current password</label>
                    <input id="email_current_password" name="current_password" type="password" required autocomplete="current-password"
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-gray-500">Needed to confirm it's really you.</p>
                </div>
                <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Update email</button>
            </form>
        </section>

        {{-- Password --}}
        <section class="rounded-lg border bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Change password</h2>

            <form method="POST" action="{{ route('account.password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium">Current password</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium">New password</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-gray-500">At least 8 characters.</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Update password</button>
                    <a href="{{ route('dashboard.index') }}" class="text-sm text-gray-600 hover:underline">Back to dashboard</a>
                </div>
            </form>
        </section>
    </div>
@endsection
