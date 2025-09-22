<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $this->call(SpecialUsersSeeder::class);

        // Seed sample data
        $this->seedSampleData();
    }

    private function seedSampleData()
    {
        // Create sample clients
        $clients = [
            ['name' => 'Metro Bank', 'branch' => 'Samapaloc'],
            ['name' => 'BDO', 'branch' => 'Makati'],
            ['name' => 'PNB', 'branch' => 'Quezon City'],
            ['name' => 'UnionBank', 'branch' => 'Pasig'],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }

        // Create sample inventory
        $inventories = [
            ['brand' => 'Staples', 'description' => 'Standard stapler clips', 'category' => 'Office Supplies', 'quantity' => 100, 'status' => 'normal'],
            ['brand' => 'HP', 'description' => 'Inkjet printer cartridge', 'category' => 'Printer Supplies', 'quantity' => 50, 'status' => 'normal'],
            ['brand' => 'Post-It', 'description' => 'Sticky notes 3x3', 'category' => 'Office Supplies', 'quantity' => 200, 'status' => 'normal'],
            ['brand' => 'Dixon', 'description' => 'Ticonderoga pencils', 'category' => 'Writing Instruments', 'quantity' => 150, 'status' => 'normal'],
            ['brand' => 'Avery', 'description' => 'White labels 1x2.5', 'category' => 'Labels', 'quantity' => 75, 'status' => 'normal'],
            ['brand' => 'Brother', 'description' => 'Thermal transfer tape', 'category' => 'Printer Supplies', 'quantity' => 25, 'status' => 'critical'],
            ['brand' => 'Pilot', 'description' => 'G2 gel pens', 'category' => 'Writing Instruments', 'quantity' => 0, 'status' => 'out_of_stock'],
        ];

        foreach ($inventories as $inventoryData) {
            Inventory::create($inventoryData);
        }

        // Create sample expenses
        $sampleExpenses = [
            ['client_id' => 1, 'inventory_id' => 1, 'quantity_used' => 10, 'cost_per_unit' => 0.50, 'total_cost' => 5.00, 'released_at' => now()->subDays(5)],
            ['client_id' => 1, 'inventory_id' => 2, 'quantity_used' => 5, 'cost_per_unit' => 25.00, 'total_cost' => 125.00, 'released_at' => now()->subDays(3)],
            ['client_id' => 2, 'inventory_id' => 3, 'quantity_used' => 20, 'cost_per_unit' => 1.00, 'total_cost' => 20.00, 'released_at' => now()->subDays(7)],
            ['client_id' => 3, 'inventory_id' => 4, 'quantity_used' => 15, 'cost_per_unit' => 0.75, 'total_cost' => 11.25, 'released_at' => now()->subDays(2)],
            ['client_id' => 4, 'inventory_id' => 5, 'quantity_used' => 8, 'cost_per_unit' => 2.50, 'total_cost' => 20.00, 'released_at' => now()->subDays(1)],
        ];

        foreach ($sampleExpenses as $expenseData) {
            Expense::create($expenseData);
        }

        // Update inventory quantities based on expenses
        foreach ($sampleExpenses as $expenseData) {
            $inventory = Inventory::find($expenseData['inventory_id']);
            $inventory->quantity -= $expenseData['quantity_used'];
            if ($inventory->quantity <= 0) {
                $inventory->quantity = 0;
                $inventory->status = 'out_of_stock';
            } elseif ($inventory->quantity <= 5) {
                $inventory->status = 'critical';
            } else {
                $inventory->status = 'normal';
            }
            $inventory->save();
        }
    }
}
