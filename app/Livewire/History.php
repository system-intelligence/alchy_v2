<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\History as HistoryModel;

class History extends Component
{
    public $histories;
    public $viewMode = 'grid'; // 'grid' or 'table'

    public function mount()
    {
        $this->loadHistories();
    }

    public function loadHistories()
    {
        $user = auth()->user();
        if ($user->isDeveloper()) {
            $this->histories = HistoryModel::with('user')->latest()->get();
        } else {
            $this->histories = HistoryModel::where('user_id', $user->id)->with('user')->latest()->get();
        }
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function render()
    {
        return view('livewire.history');
    }
}
