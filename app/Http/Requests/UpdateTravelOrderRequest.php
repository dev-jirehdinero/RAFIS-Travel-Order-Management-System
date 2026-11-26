<?php

namespace App\Http\Requests;

use App\Models\TravelOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTravelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('travelOrder')) ?? false;
    }

    /** Normalise before validating, e.g. " to-2026-13104 " -> "TO-2026-13104". */
    protected function prepareForValidation(): void
    {
        if (is_string($this->assignment_id)) {
            $this->merge(['assignment_id' => strtoupper(trim($this->assignment_id))]);
        }
    }

    public function rules(): array
    {
        /** @var TravelOrder $order */
        $order = $this->route('travelOrder');

        return [
            'assignment_id' => [
                'required',
                'string',
                'regex:/^TO-2026-\d{5}$/',
                // Keeping its own ID is fine; clashing with another order is not.
                Rule::unique('travel_orders', 'assignment_id')->ignore($order->id),
            ],
            'personnel_id' => ['required', 'integer', Rule::exists('personnel', 'id')],
            'official_station' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'assignment_id.regex' => 'The assignment ID must look like TO-2026-XXXXX (five digits).',
            'return_date.after_or_equal' => 'The return date cannot be before the departure date.',
        ];
    }
}
