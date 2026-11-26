@extends('layouts.app')

@section('title', 'RAFIS Dashboard')

@section('content')
    {{-- Global status counters --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total orders</p>
            <p class="mt-1 text-3xl font-semibold">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Pending</p>
            <p class="mt-1 text-3xl font-semibold">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-lg border bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Submitted / approved</p>
            <p class="mt-1 text-3xl font-semibold">{{ $stats['submitted'] }}</p>
        </div>
    </section>

    {{-- 1. Accomplishment Summary: OIC / Admin only --}}
    @can('viewAny', App\Models\TravelOrder::class)
        <section>
            <h2 class="mb-3 text-lg font-semibold">Accomplishment Summary</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($personnel as $person)
                    @php
                        $pct = $person->completionPercentage();
                        $barColor = $pct >= 80 ? 'bg-green-500' : ($pct >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                    @endphp
                    <article class="min-w-0 rounded-lg border bg-white p-4">
                        <h3 class="truncate font-semibold" title="{{ $person->name }}">{{ $person->name }}</h3>
                        <p class="truncate text-sm text-gray-500" title="{{ $person->position }}">{{ $person->position }}</p>

                        <div class="mt-4 flex items-baseline justify-between">
                            <span class="text-sm text-gray-600">Submitted / Total</span>
                            <span class="font-semibold">{{ $person->completed_orders_count }} / {{ $person->travel_orders_count }}</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200"
                             role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="mt-1 text-right text-xs text-gray-500">{{ $pct }}%</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endcan

    {{-- 2. Travel Order Registry --}}
    <section class="rounded-lg border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
            <h2 class="text-lg font-semibold">Travel Order Registry</h2>

            @can('create', App\Models\TravelOrder::class)
                <button type="button" onclick="document.getElementById('new-registry').showModal()"
                        class="rounded bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">+ New Registry</button>

                {{-- A real modal: close with the X, Cancel, the Esc key, or by clicking outside it. --}}
                <dialog id="new-registry" class="m-auto w-full max-w-md rounded-lg p-0 shadow-xl backdrop:bg-black/40">
                    <form method="POST" action="{{ route('travel-orders.store') }}" class="space-y-3 p-5">
                        @csrf
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">New registry</h3>
                            <button type="button" onclick="this.closest('dialog').close()" aria-label="Close"
                                    class="rounded px-2 py-1 text-xl leading-none text-gray-500 hover:bg-gray-100">&times;</button>
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="assignment_id">Assignment ID</label>
                            <input id="assignment_id" name="assignment_id" value="{{ old('assignment_id') }}"
                                   placeholder="TO-2026-13104" required class="mt-1 w-full rounded border px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="personnel_id">Personnel</label>
                            <select id="personnel_id" name="personnel_id" required class="mt-1 w-full rounded border px-2 py-1.5 text-sm">
                                <option value="">Select…</option>
                                @foreach ($personnelOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('personnel_id') == $option->id)>{{ $option->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="official_station">Official station</label>
                            <input id="official_station" name="official_station" value="{{ old('official_station') }}" required
                                   class="mt-1 w-full rounded border px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="destination">Destination</label>
                            <input id="destination" name="destination" value="{{ old('destination') }}" required
                                   class="mt-1 w-full rounded border px-2 py-1.5 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium" for="departure_date">Departure</label>
                                <input id="departure_date" type="date" name="departure_date" value="{{ old('departure_date') }}" required
                                       class="mt-1 w-full rounded border px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium" for="return_date">Return</label>
                                <input id="return_date" type="date" name="return_date" value="{{ old('return_date') }}" required
                                       class="mt-1 w-full rounded border px-2 py-1.5 text-sm">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" onclick="this.closest('dialog').close()"
                                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50">Cancel</button>
                            <button class="rounded bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </dialog>

                <script>
                    (function () {
                        var dialog = document.getElementById('new-registry');
                        // Clicking the dark area outside the form closes it.
                        dialog.addEventListener('click', function (e) { if (e.target === dialog) dialog.close(); });
                        @if (old('assignment_id') !== null && $errors->any())
                            // The form was rejected by validation: reopen it with the typed values and the errors above.
                            dialog.showModal();
                        @endif
                    })();
                </script>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="w-32 border-b px-4 py-2">Assignment ID</th>
                        <th class="w-48 border-b px-4 py-2">Personnel</th>
                        <th class="w-36 border-b px-4 py-2">Station</th>
                        <th class="w-44 border-b px-4 py-2">Departure / Return</th>
                        <th class="w-28 border-b px-4 py-2">Status</th>
                        <th class="w-64 border-b px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($orders as $order)
                        <tr class="align-top hover:bg-gray-50">
                            <td class="truncate px-4 py-3 font-mono" title="{{ $order->assignment_id }}">{{ $order->assignment_id }}</td>
                            <td class="truncate px-4 py-3" title="{{ $order->personnel->name }}">{{ $order->personnel->name }}</td>
                            <td class="truncate px-4 py-3" title="{{ $order->official_station }}">{{ $order->official_station }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $order->departure_date->format('M j, Y') }}
                                <span class="text-gray-400">→</span>
                                {{ $order->return_date->format('M j, Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block whitespace-nowrap rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $order->status->pillClasses() }}">
                                    {{ $order->status->value }}
                                </span>
                            </td>
                            <td class="space-y-2 px-4 py-3">
                                <div class="flex gap-3">
                                    @if ($order->google_drive_link)
                                        <a href="{{ $order->google_drive_link }}" target="_blank" rel="noopener noreferrer"
                                           class="text-blue-600 hover:underline">View file</a>
                                    @endif
                                    @can('update', $order)
                                        <a href="{{ route('travel-orders.edit', $order) }}" class="text-blue-600 hover:underline">Edit</a>
                                    @endcan
                                </div>

                                @can('update', $order)
                                    <form method="POST" action="{{ route('travel-orders.status', $order) }}" class="flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="min-w-0 flex-1 rounded border px-2 py-1 text-xs">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->value }}</option>
                                            @endforeach
                                        </select>
                                        <button class="rounded border px-2 py-1 text-xs hover:bg-gray-50">Update</button>
                                    </form>
                                @endcan

                                @can('upload', $order)
                                    <form method="POST" action="{{ route('travel-orders.upload', $order) }}" enctype="multipart/form-data" class="flex gap-2">
                                        @csrf
                                        <input type="file" name="file" required class="min-w-0 flex-1 text-xs">
                                        <button class="rounded border px-2 py-1 text-xs hover:bg-gray-50">Upload</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No travel orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="border-t px-4 py-3">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
