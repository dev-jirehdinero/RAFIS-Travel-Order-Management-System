<?php

namespace App\Http\Controllers;

use App\Enums\TravelOrderStatus;
use App\Models\Personnel;
use App\Models\TravelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $seesAll = $user->canViewAllOrders();

        // Accomplishment Summary: one query, counts done in SQL (no N+1).
        $personnel = $seesAll
            ? Personnel::query()
                ->withCount([
                    'travelOrders',
                    'travelOrders as completed_orders_count' => fn ($q) => $q->whereIn('status', TravelOrderStatus::completedValues()),
                ])
                ->orderBy('name')
                ->get()
            : collect();

        // Global status variables in a single aggregate query.
        $row = TravelOrder::query()
            ->visibleTo($user)
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as pending', [TravelOrderStatus::NotStarted->value])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) as submitted',
                TravelOrderStatus::completedValues()
            )
            ->first();

        $stats = [
            'total' => (int) $row->total,
            'pending' => (int) $row->pending,
            'submitted' => (int) $row->submitted,
        ];

        // Registry history: eager-load personnel so rows don't trigger extra queries.
        $orders = TravelOrder::query()
            ->visibleTo($user)
            ->with('personnel:id,name')
            ->latest('departure_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $personnelOptions = $user->can('create', TravelOrder::class)
            ? Personnel::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('dashboard', [
            'personnel' => $personnel,
            'stats' => $stats,
            'orders' => $orders,
            'personnelOptions' => $personnelOptions,
            'statuses' => TravelOrderStatus::cases(),
            'seesAll' => $seesAll,
        ]);
    }
}
