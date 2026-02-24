<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\History;
use App\Models\MaterialReleaseApproval;

class CleanupRedundantHistoryEntries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'history:cleanup-redundant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up redundant Material Release Request Created entries for already approved releases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up redundant history entries...');

        // Find all "Material Release Request Created" entries that have a corresponding "Material Release Completed" entry
        // This indicates the release was auto-approved and the "Request Created" entry is redundant
        $requestCreatedEntries = History::where('action', 'Material Release Request Created')
            ->where('model', 'MaterialReleaseApproval')
            ->get();

        $redundantEntries = collect();

        foreach ($requestCreatedEntries as $entry) {
            // Check if there's a "Material Release Completed" entry for the same approval
            $completedEntry = History::where('action', 'Material Release Completed')
                ->where('model', 'MaterialReleaseApproval')
                ->where('model_id', $entry->model_id)
                ->first();

            if ($completedEntry) {
                $redundantEntries->push($entry);
            }
        }

        $count = $redundantEntries->count();

        if ($count === 0) {
            $this->info('No redundant entries found.');
            return;
        }

        $this->warn("Found {$count} redundant entries:");

        foreach ($redundantEntries as $entry) {
            $this->line("  - Entry ID: {$entry->id} | Approval ID: {$entry->model_id} | Created: {$entry->created_at}");
        }

        if ($this->confirm('Do you want to delete these redundant entries?')) {
            foreach ($redundantEntries as $entry) {
                $entry->delete();
            }

            $this->info("Successfully deleted {$count} redundant entries.");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
