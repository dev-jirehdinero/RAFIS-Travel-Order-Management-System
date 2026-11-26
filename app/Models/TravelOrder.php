<?php

namespace App\Models;

use App\Enums\TravelOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'personnel_id',
        'official_station',
        'google_drive_link',
        'departure_date',
        'return_date',
        'destination',
        'status',
    ];

    protected $attributes = [
        'status' => 'Not started',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'status' => TravelOrderStatus::class,
        ];
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    /** Limit to what the given user is allowed to see. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->canViewAllOrders()
            ? $query
            : $query->where('personnel_id', $user->personnel_id ?? 0);
    }
}
