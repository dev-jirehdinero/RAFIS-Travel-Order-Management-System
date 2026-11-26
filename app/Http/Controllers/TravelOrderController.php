<?php

namespace App\Http\Controllers;

use App\Enums\TravelOrderStatus;
use App\Exceptions\GoogleDriveUploadException;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderRequest;
use App\Models\Personnel;
use App\Models\TravelOrder;
use App\Services\GoogleDriveUploader;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TravelOrderController extends Controller
{
    // Laravel 11+ ships an empty base Controller, so authorize() must be pulled in.
    use AuthorizesRequests;

    public function __construct(private readonly GoogleDriveUploader $drive)
    {
    }

    public function store(StoreTravelOrderRequest $request): RedirectResponse
    {
        // Authorization already ran in the form request; only validated keys are persisted.
        $order = TravelOrder::create($request->validated());

        return redirect()
            ->route('dashboard.index')
            ->with('success', "Travel order {$order->assignment_id} was registered.");
    }

    public function edit(TravelOrder $travelOrder): View
    {
        $this->authorize('update', $travelOrder);

        return view('travel-orders.edit', [
            'order' => $travelOrder,
            'personnelOptions' => Personnel::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTravelOrderRequest $request, TravelOrder $travelOrder): RedirectResponse
    {
        // Authorization already ran in the form request.
        $travelOrder->update($request->validated());

        return redirect()
            ->route('dashboard.index')
            ->with('success', "Travel order {$travelOrder->assignment_id} was updated.");
    }

    public function updateStatus(Request $request, TravelOrder $travelOrder): RedirectResponse
    {
        $this->authorize('update', $travelOrder);

        $data = $request->validate([
            'status' => ['required', Rule::in(TravelOrderStatus::values())],
        ]);

        $travelOrder->update($data);

        return back()->with('success', "{$travelOrder->assignment_id} marked as {$data['status']}.");
    }

    public function upload(Request $request, TravelOrder $travelOrder): RedirectResponse
    {
        $this->authorize('upload', $travelOrder);

        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,jpg,jpeg,png'], // 20 MB
        ]);

        try {
            $link = $this->drive->upload($request->file('file'), $travelOrder);
        } catch (GoogleDriveUploadException $e) {
            return back()->with('error', $e->getMessage());
        }

        $travelOrder->update(['google_drive_link' => $link]);

        return back()->with('success', "File for {$travelOrder->assignment_id} uploaded to Google Drive.");
    }
}
