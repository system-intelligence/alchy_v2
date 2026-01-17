<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestInboundStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Create test inventory item
        $inventory = Inventory::create([
            'brand' => 'DEMO BRAND',
            'description' => 'DEMO MATERIAL',
            'category' => 'Laser Room',
            'quantity' => 0,
            'min_stock_level' => 5,
        ]);

        // Add first inbound stock (25 units)
        $inventory->addInboundStock(25, $user->id, [
            'cost_per_unit' => 12.50,
            'total_cost' => 312.50,
            'supplier' => 'Demo Supplier Inc.',
            'location' => 'Laser Room',
            'notes' => 'Initial bulk purchase',
        ]);

        // Simulate some time passing (for ordering)
        sleep(1);

        // Add second inbound stock (15 units) - this will show Before: 25, After: 40
        $inventory->addInboundStock(15, $user->id, [
            'cost_per_unit' => 13.00,
            'total_cost' => 195.00,
            'supplier' => 'Demo Supplier Inc.',
            'location' => 'Laser Room',
            'notes' => 'Additional stock for project',
        ]);

        // Simulate some time passing
        sleep(1);

        // Add third inbound stock (10 units) - this will show Before: 40, After: 50
        $inventory->addInboundStock(10, $user->id, [
            'cost_per_unit' => 14.00,
            'total_cost' => 140.00,
            'supplier' => 'Premium Materials Co.',
            'location' => 'Laser Room',
            'notes' => 'Premium quality units',
        ]);

        $this->command->info('Demo inbound stock data created successfully!');
        $this->command->info('Created 1 inventory item with 3 inbound stock movements.');
        $this->command->info('');
        $this->command->info('Expected table data in History -> View Details:');
        $this->command->info('Date | Type | Change | Before | After | Cost/Unit | Total Cost | User | Notes');
        $this->command->info('Will show 3 rows with progressive stock levels: 0→25, 25→40, 40→50');
        $this->command->info('');
        $this->command->info('Go to History page and click "View Details" on "Inbound Added Stock" entries.');
    }
}