<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RAFIS Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
    @auth
        <header class="border-b bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6">
                <a href="{{ route('dashboard.index') }}" class="text-lg font-semibold">RAFIS Dashboard</a>
                <div class="flex items-center gap-4 text-sm">
                    <span class="truncate text-gray-600">
                        {{ auth()->user()->name }}
                        <span class="text-gray-400">· {{ auth()->user()->role->label() }}</span>
                    </span>
                    @can('viewAny', \App\Models\User::class)
                        <a href="{{ route('users.index') }}" class="rounded border px-3 py-1 hover:bg-gray-50">Accounts</a>
                    @endcan
                    <a href="{{ route('account.edit') }}" class="rounded border px-3 py-1 hover:bg-gray-50">My account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded border px-3 py-1 hover:bg-gray-50">Log out</button>
                    </form>
                </div>
            </div>
        </header>
    @endauth

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6">
        @if (session('success'))
            <div class="rounded border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
