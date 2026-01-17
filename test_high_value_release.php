<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inventory;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

// Test high value material release
echo "Testing high value material release...\n\n";  

// Test data
$testCases = [
    [
        'quantity' => 1000,
        'cost_per_unit' => 5000.00,
        'description' => '1,000 QTY @ ₱5,000 each'
    ],
    [
        'quantity' => 5000,
        'cost_per_unit' => 10000.00,
        'description' => '5,000 QTY @ ₱10,000 each'
    ],
    [
        'quantity' => 10000,
        'cost_per_unit' => 25000.00,
        'description' => '10,000 QTY @ ₱25,000 each'
    ],
    [
        'quantity' => 50000,
        'cost_per_unit' => 100000.00,
        'description' => '50,000 QTY @ ₱100,000 each'
    ],
];

foreach ($testCases as $testCase) {
    echo "Testing: {$testCase['description']}\n";

    // Simulate the cleaned release items format from Masterlist.php
    $releaseItems = [
        [
            'inventory_id' => 1, // Dummy ID
            'quantity_used' => $testCase['quantity'],
            'cost_per_unit' => $testCase['cost_per_unit'],
        ]
    ];

    // Test validation rules from Masterlist::saveRelease() - focus on numeric validation
    $rules = [
        'releaseItems' => 'required|array|min:1',
        'releaseItems.*.quantity_used' => 'required|integer|min:1',
        'releaseItems.*.cost_per_unit' => 'required|numeric|min:0',
    ];

    $data = [
        'releaseItems' => $releaseItems,
    ];

    $validator = Validator::make($data, $rules);

    if ($validator->fails()) {
        echo "❌ Validation failed: " . json_encode($validator->errors()->all()) . "\n";
    } else {
        echo "✅ Validation passed\n";

        // Calculate total cost
        $totalCost = $testCase['quantity'] * $testCase['cost_per_unit'];
        echo "   Total cost: ₱" . number_format($totalCost, 2) . "\n";

        // Check if total cost fits in database decimal(15,2)
        $maxDecimal = 99999999999999.99; // 15 digits total, 2 decimal places
        if ($totalCost > $maxDecimal) {
            echo "⚠️  Total cost exceeds database decimal(15,2) limit!\n";
        }

        // Check if quantity fits in database integer
        $maxInt = 2147483647; // MySQL INT max
        if ($testCase['quantity'] > $maxInt) {
            echo "⚠️  Quantity exceeds database INTEGER limit!\n";
        }

        // Check if cost_per_unit fits in database decimal(15,2)
        if ($testCase['cost_per_unit'] > $maxDecimal) {
            echo "⚠️  Cost per unit exceeds database decimal(15,2) limit!\n";
        }
    }

    echo "\n";
}

// Test input formatting functions
echo "Testing input formatting...\n";

$testInputs = [
    '1,000' => 1000,
    '5,000' => 5000,
    '10,000' => 10000,
    '1,000,000' => 1000000,
    '1,000.50' => 1000.50,
    '5,000.75' => 5000.75,
];

foreach ($testInputs as $input => $expected) {
    $cleaned = str_replace(',', '', $input);
    $parsed = is_numeric($cleaned) ? (strpos($cleaned, '.') !== false ? (float)$cleaned : (int)$cleaned) : null;

    if ($parsed === $expected) {
        echo "✅ '$input' -> $parsed\n";
    } else {
        echo "❌ '$input' -> $parsed (expected $expected)\n";
    }
}

echo "\nTest completed.\n";