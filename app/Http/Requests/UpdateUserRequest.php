<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->target()) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    private function target(): User
    {
        return $this->route('user');
    }

    /** A staff account needs a staff record; an Admin turned into Staff may not have one yet. */
    private function needsNewStaffRecord(): bool
    {
        return $this->input('role') === Role::Staff->value && $this->target()->personnel_id === null;
    }

    /** @return string[] */
    private function allowedRoles(): array
    {
        // Nobody can change their own role (stops an Admin locking themselves out).
        return $this->target()->is($this->user())
            ? [$this->target()->role->value]
            : $this->user()->assignableRoles();
    }

    public function rules(): array
    {
        $target = $this->target();

        return [
            'name' => [
                'required', 'string', 'max:255',
                // The staff record is renamed too, and those names must stay unique.
                Rule::when(
                    $target->personnel_id !== null || $this->needsNewStaffRecord(),
                    [Rule::unique('personnel', 'name')->ignore($target->personnel_id)]
                ),
            ],
            'role' => ['required', Rule::in($this->allowedRoles())],
            'position' => [
                Rule::requiredIf(fn () => $this->needsNewStaffRecord()),
                'nullable', 'string', 'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A staff record with this name already exists.',
            'position.required' => 'Enter a position, because this account has no staff record yet.',
            'role.in' => 'You are not allowed to give this account that role.',
        ];
    }
}
