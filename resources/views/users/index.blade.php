@extends('layouts.app')

@section('title', 'Accounts · RAFIS')

@section('content')
    <section class="rounded-lg border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
            <h1 class="text-lg font-semibold">Accounts</h1>
            <a href="{{ route('users.create') }}"
               class="rounded bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">+ New account</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="w-64 border-b px-4 py-2">Name</th>
                        <th class="w-64 border-b px-4 py-2">Email</th>
                        <th class="w-36 border-b px-4 py-2">Role</th>
                        <th class="w-64 border-b px-4 py-2">Staff record</th>
                        <th class="w-20 border-b px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="truncate px-4 py-3" title="{{ $user->name }}">{{ $user->name }}</td>
                            <td class="truncate px-4 py-3" title="{{ $user->email }}">{{ $user->email }}</td>
                            <td class="px-4 py-3">{{ $user->role->label() }}</td>
                            <td class="truncate px-4 py-3 text-gray-600" title="{{ $user->personnel?->name }}">{{ $user->personnel?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @can('update', $user)
                                    <a href="{{ route('users.edit', $user) }}" class="text-blue-600 hover:underline">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No accounts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t px-4 py-3">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
