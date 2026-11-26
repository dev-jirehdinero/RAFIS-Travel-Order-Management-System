<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    /** Admin accounts are not tied to a staff record; everyone else is. */
    private function needsNewStaffRecord(): bool
    {
        return $this->input('role') !== Role::Admin->value && ! $this->filled('personnel_id');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                // When a new staff record is created it uses this name, and those must be unique.
                Rule::when($this->needsNewStaffRecord(), [Rule::unique('personnel', 'name')]),
            ],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::min(8)],
            // An OIC can only create staff; only an Admin can create OIC/Admin accounts.
            'role' => ['required', Rule::in($this->user()->assignableRoles())],
            'personnel_id' => [
                'nullable', 'integer',
                Rule::exists('personnel', 'id'),
                Rule::unique('users', 'personnel_id'), // one account per staff record
            ],
            'position' => [
                Rule::requiredIf(fn () => $this->needsNewStaffRecord()),
                'nullable', 'string', 'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A staff record with this name already exists. Pick it from the "Staff record" list instead.',
            'personnel_id.unique' => 'That staff record already has an account.',
            'position.required' => 'Enter a position for the new staff record.',
            'role.in' => 'You are not allowed to create an account with that role.',
        ];
    }
}
