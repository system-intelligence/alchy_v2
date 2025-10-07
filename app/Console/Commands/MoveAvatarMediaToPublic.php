<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MoveAvatarMediaToPublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:move-avatar-media-to-public';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move existing avatar media from private to public disk';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Moving avatar media to public disk...');

        $media = Media::where('collection_name', 'avatar')->where('disk', '!=', 'public')->get();

        foreach ($media as $item) {
            $oldPath = $item->getPath();
            $newPath = str_replace(storage_path('app/private'), storage_path('app/public'), $oldPath);

            // Ensure directory exists
            $newDir = dirname($newPath);
            if (!is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }

            // Copy file
            if (copy($oldPath, $newPath)) {
                // Update media record
                $item->update(['disk' => 'public']);
                // Delete old file
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $this->info("Moved media ID {$item->id} to public disk");
            } else {
                $this->error("Failed to move media ID {$item->id}");
            }
        }

        $this->info('Done!');
    }
}
