<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StockMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $movementType = $this->faker->randomElement(['inbound', 'outbound', 'adjustment']);
        $quantity = $this->faker->numberBetween(1, 100);
        $previousQuantity = $this->faker->numberBetween(0, 50);

        // For outbound, make quantity negative
        if ($movementType === 'outbound') {
            $quantity = -$quantity;
        }

        return [
            'inventory_id' => Inventory::factory(),
            'user_id' => User::factory(),
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $previousQuantity + $quantity,
            'cost_per_unit' => $this->faker->randomFloat(2, 10, 500),
            'total_cost' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['cost_per_unit'];
            },
            'supplier' => $this->faker->optional(0.7)->company(),
            'location' => $this->faker->optional(0.8)->randomElement(Inventory::CATEGORIES),
            'notes' => $this->faker->optional(0.6)->sentence(),
            'date_received' => $this->faker->optional(0.5)->date(),
        ];
    }

    /**
     * Create an inbound movement.
     */
    public function inbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => 'inbound',
            'quantity' => abs($attributes['quantity'] ?? $this->faker->numberBetween(1, 100)),
        ]);
    }

    /**
     * Create an outbound movement.
     */
    public function outbound(): static
    {
        return $this->state(fn (array $attributes) => [
            'movement_type' => 'outbound',
            'quantity' => -abs($attributes['quantity'] ?? $this->faker->numberBetween(1, 100)),
        ]);
    }
}