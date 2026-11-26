@extends('layouts.app')

@section('title', 'Edit travel order · RAFIS')

@section('content')
    <div class="mx-auto max-w-lg rounded-lg border bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-xl font-semibold">Edit travel order</h1>
        <p class="mb-4 text-sm text-gray-500">
            Status changes and file uploads are done from the registry table.
            @if ($order->google_drive_link)
                An already-uploaded file keeps its old name in Google Drive if you change the Assignment ID.
            @endif
        </p>

        <form method="POST" action="{{ route('travel-orders.update', $order) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="assignment_id" class="block text-sm font-medium">Assignment ID</label>
                <input id="assignment_id" name="assignment_id" value="{{ old('assignment_id', $order->assignment_id) }}" required
                       placeholder="TO-2026-13104" class="mt-1 w-full rounded border px-3 py-2 text-sm">
            </div>

            <div>
                <label for="personnel_id" class="block text-sm font-medium">Personnel</label>
                <select id="personnel_id" name="personnel_id" required class="mt-1 w-full rounded border px-3 py-2 text-sm">
                    @foreach ($personnelOptions as $option)
                        <option value="{{ $option->id }}" @selected((int) old('personnel_id', $order->personnel_id) === $option->id)>{{ $option->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="official_station" class="block text-sm font-medium">Official station</label>
                <input id="official_station" name="official_station" value="{{ old('official_station', $order->official_station) }}" required
                       class="mt-1 w-full rounded border px-3 py-2 text-sm">
            </div>

            <div>
                <label for="destination" class="block text-sm font-medium">Destination</label>
                <input id="destination" name="destination" value="{{ old('destination', $order->destination) }}" required
                       class="mt-1 w-full rounded border px-3 py-2 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="departure_date" class="block text-sm font-medium">Departure</label>
                    <input id="departure_date" type="date" name="departure_date"
                           value="{{ old('departure_date', $order->departure_date->format('Y-m-d')) }}" required
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="return_date" class="block text-sm font-medium">Return</label>
                    <input id="return_date" type="date" name="return_date"
                           value="{{ old('return_date', $order->return_date->format('Y-m-d')) }}" required
                           class="mt-1 w-full rounded border px-3 py-2 text-sm">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button class="rounded bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Save changes</button>
                <a href="{{ route('dashboard.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
