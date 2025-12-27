<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\History as HistoryModel;

class History extends Component
{
    public $histories;
    public $viewMode = 'grid'; // 'grid' or 'table'
    public $showChangeModal = false;
    public $selectedHistory = null;

    protected $listeners = [
        'historyCreated' => 'loadHistories',
        'refreshHistory' => 'refreshHistories',
    ];

    public function mount()
    {
        $this->loadHistories();
    }

    public function loadHistories()
    {
        \Log::info('Loading histories for user: ' . auth()->id());
        $user = auth()->user();

        if ($user->role === 'system_admin') {
            // System admins see ALL histories across the system
            $this->histories = HistoryModel::with('user')->latest()->get();
        } elseif ($user->role === 'user') {
            // Users see their own histories + all approval request histories
            $userHistories = HistoryModel::where('user_id', $user->id)->with('user');
            $approvalHistories = HistoryModel::where('model', 'MaterialReleaseApproval')->with('user');
            $this->histories = $userHistories->union($approvalHistories)->latest()->get();
        } else {
            // Developers only see their own histories
            $this->histories = HistoryModel::where('user_id', $user->id)->with('user')->latest()->get();
        }

        \Log::info('Loaded ' . $this->histories->count() . ' history entries');
    }

    public function refreshHistories()
    {
        \Log::info('refreshHistories called via Livewire event');
        $this->loadHistories();
        
        // Dispatch a browser event to confirm refresh
        $this->dispatch('history-refreshed', [
            'message' => 'History updated',
            'count' => $this->histories->count()
        ]);
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function showChangeDetails($historyId)
    {
        $this->selectedHistory = HistoryModel::find($historyId);
        $this->showChangeModal = true;
    }

    public function closeChangeModal()
    {
        $this->showChangeModal = false;
        $this->selectedHistory = null;
    }

    public function getHighlightedDiff($oldValue, $newValue)
    {
        if (is_array($oldValue) || is_object($oldValue) || is_array($newValue) || is_object($newValue)) {
            return htmlspecialchars($oldValue ?? 'N/A') . ' → ' . htmlspecialchars($newValue ?? 'N/A');
        }

        if (!is_string($oldValue) || !is_string($newValue)) {
            return htmlspecialchars($oldValue ?? 'N/A') . ' → ' . htmlspecialchars($newValue ?? 'N/A');
        }

        // Simple word-based diff for strings
        $oldWords = explode(' ', $oldValue);
        $newWords = explode(' ', $newValue);

        $diff = [];
        $i = 0;
        $j = 0;

        while ($i < count($oldWords) || $j < count($newWords)) {
            if ($i < count($oldWords) && $j < count($newWords) && $oldWords[$i] === $newWords[$j]) {
                $diff[] = htmlspecialchars($oldWords[$i]);
                $i++;
                $j++;
            } elseif ($i < count($oldWords)) {
                // Removed word
                $diff[] = '<strike class="bg-red-100 text-red-800 px-1 rounded border border-red-300">' . htmlspecialchars($oldWords[$i]) . '</strike>';
                $i++;
            } else {
                // Added word
                $diff[] = '<span class="bg-green-100 text-green-800 px-1 rounded border border-green-300">' . htmlspecialchars($newWords[$j]) . '</span>';
                $j++;
            }
        }

        return implode(' ', $diff);
    }

    public function render()
    {
        return view('livewire.history');
    }
}
