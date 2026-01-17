<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Inventory;
use App\Models\History;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryStockMovementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbound_stock_addition_creates_history_and_stock_movement(): void
    {
        // Create test user
        $user = User::factory()->create();

        // Create inventory
        $inventory = Inventory::factory()->create([
            'brand' => 'Test Brand',
            'description' => 'Test Description',
            'quantity' => 5,
        ]);

        // Add inbound stock using the model's method
        $result = $inventory->addInboundStock(5, $user->id, [
            'cost_per_unit' => 100.00,
            'total_cost' => 500.00,
            'supplier' => 'Test Supplier',
            'location' => 'Test Location',
            'notes' => 'Test notes',
        ]);

        // Assert the operation was successful
        $this->assertTrue($result);

        // Verify inventory quantity was updated
        $inventory->refresh();
        $this->assertEquals(10, $inventory->quantity);

        // Verify StockMovement was created
        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $inventory->id,
            'user_id' => $user->id,
            'movement_type' => 'inbound',
            'quantity' => 5,
            'previous_quantity' => 5,
            'new_quantity' => 10,
            'cost_per_unit' => 100.00,
            'total_cost' => 500.00,
            'supplier' => 'Test Supplier',
            'notes' => 'Test notes',
        ]);

        // Verify History was created
        $this->assertDatabaseHas('histories', [
            'user_id' => $user->id,
            'action' => 'Inbound Stock Added',
            'model' => 'inventory',
            'model_id' => $inventory->id,
        ]);

        // Get the history entry
        $history = History::where('model_id', $inventory->id)
            ->where('action', 'Inbound Stock Added')
            ->first();

        $this->assertNotNull($history);

        // Verify history changes contain expected data
        $changes = $history->changes;
        $this->assertIsArray($changes);
        $this->assertEquals(5, $changes['quantity']);
        $this->assertEquals('TEST BRAND - TEST DESCRIPTION', $changes['inventory_item']);
        $this->assertEquals(100.00, $changes['cost_per_unit']);
        $this->assertEquals(500.00, $changes['total_cost']);
        $this->assertEquals('Test Supplier', $changes['supplier']);
        $this->assertEquals('Test Location', $changes['location']);
        $this->assertEquals('Test notes', $changes['notes']);
        $this->assertArrayHasKey('stock_movement_id', $changes);

        // Verify the stock_movement_id references the created movement
        $stockMovement = StockMovement::find($changes['stock_movement_id']);
        $this->assertNotNull($stockMovement);
        $this->assertEquals('inbound', $stockMovement->movement_type);
    }

    public function test_stock_movements_table_columns_structure(): void
    {
        // Create test data
        $user = User::factory()->create();
        $inventory = Inventory::factory()->create(['quantity' => 0]);

        // Create multiple stock movements
        $movement1 = StockMovement::create([
            'inventory_id' => $inventory->id,
            'user_id' => $user->id,
            'movement_type' => 'inbound',
            'quantity' => 10,
            'previous_quantity' => 0,
            'new_quantity' => 10,
            'cost_per_unit' => 50.00,
            'total_cost' => 500.00,
            'supplier' => 'Test Supplier',
            'notes' => 'Test inbound movement',
        ]);

        $movement2 = StockMovement::create([
            'inventory_id' => $inventory->id,
            'user_id' => $user->id,
            'movement_type' => 'outbound',
            'quantity' => -5,
            'previous_quantity' => 10,
            'new_quantity' => 5,
            'cost_per_unit' => 50.00,
            'total_cost' => 250.00,
            'notes' => 'Test outbound movement',
        ]);

        // Create history entry
        $history = History::create([
            'user_id' => $user->id,
            'action' => 'Inbound Stock Added',
            'model' => 'inventory',
            'model_id' => $inventory->id,
            'old_values' => ['quantity' => 0],
            'changes' => [
                'quantity' => 10,
                'stock_movement_id' => $movement1->id,
            ],
        ]);

        // Verify StockMovement model has correct attributes
        $this->assertEquals('Inbound', $movement1->movement_type_display);
        $this->assertEquals(10, $movement1->quantity_change);
        $this->assertEquals('Outbound', $movement2->movement_type_display);
        $this->assertEquals(-5, $movement2->quantity_change);

        // Verify all required attributes are present
        $this->assertNotNull($movement1->created_at);
        $this->assertNotNull($movement1->user);
        $this->assertEquals($user->name, $movement1->user->name);

        // Test that the query in the view would work
        $inventoryFromDb = Inventory::find($inventory->id);
        $movements = $inventoryFromDb ? StockMovement::where('inventory_id', $inventoryFromDb->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get() : collect();

        $this->assertCount(2, $movements);
        $this->assertEquals('outbound', $movements->first()->movement_type); // Most recent first
        $this->assertEquals('inbound', $movements->last()->movement_type);
    }

    public function test_history_view_displays_correct_action_description(): void
    {
        // Create test data
        $user = User::factory()->create();
        $inventory = Inventory::factory()->create();

        // Create history entry
        $history = History::create([
            'user_id' => $user->id,
            'action' => 'Inbound Stock Added',
            'model' => 'inventory',
            'model_id' => $inventory->id,
            'old_values' => ['quantity' => 0],
            'changes' => ['quantity' => 5],
        ]);

        // Verify the action description is correct
        $this->assertEquals('Inbound Added Stock', $history->action_description);

        // Test other actions for comparison
        $history2 = History::create([
            'user_id' => $user->id,
            'action' => 'Outbound Stock Removed',
            'model' => 'inventory',
            'model_id' => $inventory->id,
            'changes' => ['quantity' => 3],
        ]);

        $this->assertEquals('Outbound Stock Removed', $history2->action_description);
    }

    public function test_inbound_stock_history_modal_displays_complete_table(): void
    {
        // Create test user and inventory
        $user = User::factory()->create();
        $inventory = Inventory::factory()->create([
            'brand' => 'Test Brand',
            'description' => 'Test Item',
            'quantity' => 5,
        ]);

        // Create multiple stock movements for the inventory
        $inboundMovement = StockMovement::factory()->create([
            'inventory_id' => $inventory->id,
            'user_id' => $user->id,
            'movement_type' => 'inbound',
            'quantity' => 10,
            'previous_quantity' => 0,
            'new_quantity' => 10,
            'cost_per_unit' => 25.50,
            'total_cost' => 255.00,
            'supplier' => 'Test Supplier Inc.',
            'notes' => 'Bulk purchase for project',
        ]);

        $outboundMovement = StockMovement::factory()->create([
            'inventory_id' => $inventory->id,
            'user_id' => $user->id,
            'movement_type' => 'outbound',
            'quantity' => -3,
            'previous_quantity' => 10,
            'new_quantity' => 7,
            'cost_per_unit' => 25.50,
            'total_cost' => 76.50,
            'notes' => 'Used in construction',
        ]);

        // Create history entry for inbound stock addition
        $history = History::create([
            'user_id' => $user->id,
            'action' => 'Inbound Stock Added',
            'model' => 'inventory',
            'model_id' => $inventory->id,
            'old_values' => ['quantity' => 0],
            'changes' => [
                'quantity' => 10,
                'inventory_item' => 'TEST BRAND - TEST ITEM',
                'cost_per_unit' => 25.50,
                'total_cost' => 255.00,
                'supplier' => 'Test Supplier Inc.',
                'location' => 'Laser Room',
                'notes' => 'Bulk purchase for project',
                'stock_movement_id' => $inboundMovement->id,
            ],
        ]);

        // Simulate the data that would be available in the view
        $inventoryFromDb = Inventory::find($inventory->id);
        $movements = $inventoryFromDb ? StockMovement::where('inventory_id', $inventoryFromDb->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get() : collect();

        // Verify we have the expected movements
        $this->assertCount(2, $movements);

        // Verify the table data structure for each movement
        $firstMovement = $movements->first(); // Most recent (outbound)
        $secondMovement = $movements->last(); // Older (inbound)

        // Test outbound movement data
        $this->assertEquals('outbound', $firstMovement->movement_type);
        $this->assertEquals('Outbound', $firstMovement->movement_type_display);
        $this->assertEquals(-3, $firstMovement->quantity_change);
        $this->assertEquals(10, $firstMovement->previous_quantity);
        $this->assertEquals(7, $firstMovement->new_quantity);
        $this->assertEquals(25.50, $firstMovement->cost_per_unit);
        $this->assertEquals(76.50, $firstMovement->total_cost);
        $this->assertEquals($user->name, $firstMovement->user->name);
        $this->assertEquals('Used in construction', $firstMovement->notes);

        // Test inbound movement data
        $this->assertEquals('inbound', $secondMovement->movement_type);
        $this->assertEquals('Inbound', $secondMovement->movement_type_display);
        $this->assertEquals(10, $secondMovement->quantity_change);
        $this->assertEquals(0, $secondMovement->previous_quantity);
        $this->assertEquals(10, $secondMovement->new_quantity);
        $this->assertEquals(25.50, $secondMovement->cost_per_unit);
        $this->assertEquals(255.00, $secondMovement->total_cost);
        $this->assertEquals($user->name, $secondMovement->user->name);
        $this->assertEquals('Bulk purchase for project', $secondMovement->notes);
        $this->assertEquals('Test Supplier Inc.', $secondMovement->supplier);

        // Verify date formatting would work
        $dateFormatted = $secondMovement->created_at->format('M d, Y H:i');
        $this->assertIsString($dateFormatted);
        $this->assertMatchesRegularExpression('/^\w{3} \d{1,2}, \d{4} \d{1,2}:\d{2}$/', $dateFormatted);

        // Verify cost formatting (cast to decimal:2 so they're strings)
        $this->assertIsString($secondMovement->cost_per_unit);
        $this->assertIsString($secondMovement->total_cost);
        $this->assertEquals('25.50', $secondMovement->cost_per_unit);
        $this->assertEquals('255.00', $secondMovement->total_cost);

        // Verify the history entry exists and has correct data
        $this->assertDatabaseHas('histories', [
            'id' => $history->id,
            'action' => 'Inbound Stock Added',
            'model' => 'inventory',
            'model_id' => $inventory->id,
        ]);

        // Verify action description
        $this->assertEquals('Inbound Added Stock', $history->action_description);
    }
}