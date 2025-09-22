<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Inventory;
use App\Models\History;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $data = [];

        if ($user->isDeveloper()) {
            $data['users'] = User::all();
            $data['logs'] = History::latest()->take(10)->get();
        } elseif ($user->isSystemAdmin()) {
            $data['inventory_count'] = Inventory::count();
            $data['low_stock'] = Inventory::where('quantity', '<', 10)->count();
        } else {
            $data['recent_expenses'] = $user->expenses ?? [];
        }

        return view('livewire.dashboard', compact('data'));
    }
}
