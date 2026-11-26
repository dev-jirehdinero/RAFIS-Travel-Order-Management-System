<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'personnel_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => 'staff',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isOic(): bool
    {
        return $this->role === Role::Oic;
    }

    /** OIC and Admin can see every staff member's entries and the summary grid. */
    public function canViewAllOrders(): bool
    {
        return $this->isAdmin() || $this->isOic();
    }

    /**
     * Role values this user may give to a NEW account:
     * an Admin can create any role, an OIC can only create staff.
     *
     * @return string[]
     */
    public function assignableRoles(): array
    {
        return $this->isAdmin()
            ? array_column(Role::cases(), 'value')
            : [Role::Staff->value];
    }
}
