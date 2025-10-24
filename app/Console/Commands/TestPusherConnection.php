<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPusherConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pusher:test {--user=1 : User ID to test with}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Pusher connection and broadcasting configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Pusher Configuration...');
        $this->newLine();

        // 1. Check configuration
        $this->info('1. Checking Pusher Configuration:');
        $driver = config('broadcasting.default');
        $appId = config('broadcasting.connections.pusher.app_id');
        $key = config('broadcasting.connections.pusher.key');
        $secret = config('broadcasting.connections.pusher.secret');
        $cluster = config('broadcasting.connections.pusher.options.cluster');
        
        $this->line("   Broadcast Driver: <fg=yellow>{$driver}</>");
        $this->line("   App ID: <fg=yellow>{$appId}</>");
        $this->line("   Key: <fg=yellow>{$key}</>");
        $this->line("   Secret: <fg=yellow>" . substr($secret, 0, 10) . "...</>");
        $this->line("   Cluster: <fg=yellow>{$cluster}</>");
        
        if ($driver !== 'pusher') {
            $this->error('   ✗ Broadcasting is not set to pusher!');
            return 1;
        }
        
        if (!$appId || !$key || !$secret) {
            $this->error('   ✗ Pusher credentials are missing!');
            return 1;
        }
        
        $this->info('   ✓ Configuration looks good!');
        $this->newLine();

        // 2. Test Pusher connection
        $this->info('2. Testing Pusher Connection:');
        try {
            $pusher = new \Pusher\Pusher(
                $key,
                $secret,
                $appId,
                [
                    'cluster' => $cluster,
                    'useTLS' => true,
                ]
            );
            
            $result = $pusher->get('/channels');
            
            if ($result) {
                $this->info('   ✓ Successfully connected to Pusher!');
                $channels = is_object($result->channels) ? get_object_vars($result->channels) : ($result->channels ?? []);
                $channelCount = count($channels);
                $this->line("   Active channels: <fg=yellow>{$channelCount}</>");
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Failed to connect to Pusher!');
            $this->error('   Error: ' . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // 3. Test broadcasting a message
        $this->info('3. Testing Message Broadcasting:');
        $userId = $this->option('user');
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            $this->warn("   ! User #{$userId} not found. Skipping broadcast test.");
        } else {
            try {
                $testChat = \App\Models\Chat::create([
                    'user_id' => $user->id,
                    'recipient_id' => $user->id,
                    'message' => 'Test message from Pusher test command at ' . now()->toDateTimeString(),
                ]);
                
                $testChat->load(['user', 'recipient']);
                
                $event = new \App\Events\MessageSent($testChat);
                broadcast($event);
                
                $this->info('   ✓ Test message broadcasted!');
                $this->line("   Message ID: <fg=yellow>{$testChat->id}</>");
                $this->line("   Event: <fg=yellow>MessageSent</>");
                
                // Clean up test message
                $testChat->delete();
                $this->line("   (Test message cleaned up)");
            } catch (\Exception $e) {
                $this->error('   ✗ Failed to broadcast message!');
                $this->error('   Error: ' . $e->getMessage());
                return 1;
            }
        }
        $this->newLine();

        // 4. Check routes
        $this->info('4. Checking Broadcasting Routes:');
        $routeExists = false;
        foreach (\Route::getRoutes() as $route) {
            if (str_contains($route->uri(), 'broadcasting/auth')) {
                $routeExists = true;
                break;
            }
        }
        
        if ($routeExists) {
            $this->info('   ✓ Broadcasting auth route is registered!');
        } else {
            $this->warn('   ! Broadcasting auth route not found. Add Broadcast::routes() to routes/web.php');
        }
        $this->newLine();

        // 5. Summary
        $this->info('═══════════════════════════════════════');
        $this->info('✅ Pusher is properly configured!');
        $this->info('═══════════════════════════════════════');
        $this->newLine();
        
        $this->line('Next steps:');
        $this->line('  1. Make sure Vite is running: <fg=yellow>npm run dev</>');
        $this->line('  2. Open browser console to see Echo connection logs');
        $this->line('  3. Test real-time messaging in the chat widget');
        $this->newLine();

        return 0;
    }
}
