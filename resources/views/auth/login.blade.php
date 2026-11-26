@extends('layouts.app')

@section('title', 'Log in · RAFIS')

@section('content')
    <div class="mx-auto mt-16 max-w-sm rounded-lg border bg-white p-6 shadow-sm">
        <h1 class="mb-4 text-xl font-semibold">Log in to RAFIS</h1>
        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       autocomplete="email"
                       class="mt-1 w-full rounded border px-3 py-2 text-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="mt-1 w-full rounded border px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>
            <button class="w-full rounded bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">Log in</button>
        </form>
    </div>
@endsection
