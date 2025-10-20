<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InventoryStatus;
use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display the system administrator dashboard.
     */
    public function index()
    {
        $metrics = Cache::remember('dashboard.metrics', now()->addMinute(), function () {
            $totalInventory = Inventory::count();

            $statusCounts = Inventory::selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            return [
                'total' => (int) $totalInventory,
                'normal' => (int) ($statusCounts[InventoryStatus::NORMAL->value] ?? 0),
                'critical' => (int) ($statusCounts[InventoryStatus::CRITICAL->value] ?? 0),
                'out_of_stock' => (int) ($statusCounts[InventoryStatus::OUT_OF_STOCK->value] ?? 0),
            ];
        });

        $metrics['attention'] = $metrics['critical'] + $metrics['out_of_stock'];

        $recentChanges = History::where('model', 'inventory')
            ->with('user:id,name')
            ->latest()
            ->limit(5)
            ->get();

        $lowStockItems = Inventory::attention()
            ->latest()
            ->limit(5)
            ->get();

        $recentInventory = Inventory::latest()
            ->limit(5)
            ->get();

        $usersCount = Cache::remember('dashboard.users_count', now()->addMinute(), fn () => User::count());

        return view('dashboard-admin', [
            'metrics' => $metrics,
            'recentChanges' => $recentChanges,
            'lowStockItems' => $lowStockItems,
            'recentInventory' => $recentInventory,
            'usersCount' => $usersCount,
        ]);
    }
}
