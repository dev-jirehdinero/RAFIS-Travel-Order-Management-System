<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Personnel extends Model
{
    use HasFactory;

    // "Personnel" is not pluralised by Laravel, so be explicit.
    protected $table = 'personnel';

    protected $fillable = [
        'name',
        'position',
        'employment_status',
        'unit_head',
        'assigned_program',
    ];

    public function travelOrders(): HasMany
    {
        return $this->hasMany(TravelOrder::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Completion percentage (0-100). Expects the withCount() aliases
     * `travel_orders_count` and `completed_orders_count` to be loaded
     * (see DashboardController); falls back to queries otherwise.
     */
    public function completionPercentage(): int
    {
        $total = $this->travel_orders_count ?? $this->travelOrders()->count();
        $done = $this->completed_orders_count
            ?? $this->travelOrders()->whereIn('status', \App\Enums\TravelOrderStatus::completedValues())->count();

        return $total > 0 ? (int) round($done / $total * 100) : 0;
    }
}
