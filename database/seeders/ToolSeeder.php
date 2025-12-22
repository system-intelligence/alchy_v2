<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            [
                'quantity' => 5,
                'brand' => 'DEWALT',
                'model' => 'DCD771C2',
                'description' => 'CORDLESS DRILL',
                'released_to' => 'John Doe',
                'release_date' => '2025-10-20',
            ],
            [
                'quantity' => 3,
                'brand' => 'MAKITA',
                'model' => 'XPH07Z',
                'description' => 'HAMMER DRILL',
                'released_to' => 'Jane Smith',
                'release_date' => '2025-10-18',
            ],
            [
                'quantity' => 10,
                'brand' => 'BOSCH',
                'model' => 'GWS 750-100',
                'description' => 'ANGLE GRINDER',
                'released_to' => null,
                'release_date' => null,
            ],
            [
                'quantity' => 2,
                'brand' => 'MILWAUKEE',
                'model' => 'M18',
                'description' => 'IMPACT DRIVER',
                'released_to' => 'Mike Johnson',
                'release_date' => '2025-10-15',
            ],
        ];

        foreach ($tools as $tool) {
            Tool::create($tool);
        }
    }
}
