<?php

namespace Tests\Feature;

use App\Models\History;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalHistoryClientProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that history changes can store and retrieve client/project data
     */
    public function test_history_can_store_client_and_project_in_json_changes()
    {
        // Create a test user first
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $historyChanges = [
            'status' => 'approved',
            'requester' => 'Ben',
            'client' => 'CHINABANK',
            'project' => 'Signage Project',
            'material' => 'TEST - TEST ITEM',
            'quantity' => 1,
            'reviewer' => 'Sino_Hahahawk_Nito',
            'reviewed_at' => now()->toDateTimeString(),
        ];

        $history = History::create([
            'user_id' => $user->id,
            'model' => 'MaterialReleaseApproval',
            'model_id' => 77,
            'action' => 'Approval Request Approved',
            'action_description' => 'Approval Request Approved',
            'changes' => json_encode($historyChanges),
        ]);

        // Verify it was saved
        $this->assertDatabaseHas('histories', [
            'model' => 'MaterialReleaseApproval',
            'model_id' => 77,
        ]);

        // Retrieve and decode
        $retrieved = History::findOrFail($history->id);
        $decodedChanges = json_decode($retrieved->changes, true);

        // Assertions
        $this->assertIsArray($decodedChanges, 'Changes should decode to array');
        $this->assertEquals('CHINABANK', $decodedChanges['client']);
        $this->assertEquals('Signage Project', $decodedChanges['project']);
        $this->assertNotEmpty($decodedChanges['client']);
        $this->assertNotEmpty($decodedChanges['project']);
    }

    /**
     * Test JSON decoding logic for client and project retrieval
     */
    public function test_json_decoding_handles_both_string_and_array()
    {
        // Test 1: String (as stored in DB)
        $jsonString = json_encode(['client' => 'Test Client', 'project' => 'Test Project']);
        
        $changes = [];
        if (is_string($jsonString)) {
            $changes = json_decode($jsonString, true) ?? [];
        }
        
        $this->assertEquals('Test Client', $changes['client']);
        $this->assertEquals('Test Project', $changes['project']);

        // Test 2: Array (if already decoded)
        $arrayData = ['client' => 'Another Client', 'project' => 'Another Project'];
        
        $changes = [];
        if (is_array($arrayData)) {
            $changes = $arrayData;
        }
        
        $this->assertEquals('Another Client', $changes['client']);
        $this->assertEquals('Another Project', $changes['project']);
    }

    /**
     * Test that N/A values are filtered out
     */
    public function test_na_values_are_treated_as_missing()
    {
        $historyChanges = [
            'client' => 'Real Client',
            'project' => 'N/A',  // Fallback for missing project
        ];

        $changes = json_decode(json_encode($historyChanges), true);

        // Client should be present
        $clientName = (!empty($changes['client']) && $changes['client'] !== 'N/A') 
            ? $changes['client'] 
            : null;

        // Project should be null (filtered out)
        $projectName = (!empty($changes['project']) && $changes['project'] !== 'N/A') 
            ? $changes['project'] 
            : null;

        $this->assertEquals('Real Client', $clientName);
        $this->assertNull($projectName);
    }

    /**
     * Test complete approval record with all required fields
     */
    public function test_complete_approval_record_structure()
    {
        // Create a test user first
        $user = \App\Models\User::create([
            'name' => 'Test Reviewer',
            'email' => 'reviewer@example.com',
            'password' => bcrypt('password'),
        ]);

        $expectedFields = [
            'status' => 'approved',
            'requester' => 'Ben',
            'client' => 'CHINABANK',
            'project' => 'Signage Project',
            'material' => 'Del Monte Tomato Sauce - 1kg Can',
            'quantity' => 1,
            'reviewer' => 'Sino_Hahahawk_Nito',
            'reviewed_at' => now()->toDateTimeString(),
        ];

        $history = History::create([
            'user_id' => $user->id,
            'model' => 'MaterialReleaseApproval',
            'model_id' => 99,
            'action' => 'Approval Request Approved',
            'action_description' => 'Approval Request Approved',
            'changes' => json_encode($expectedFields),
        ]);

        $retrieved = History::find($history->id);
        $decoded = json_decode($retrieved->changes, true);

        foreach ($expectedFields as $key => $value) {
            $this->assertArrayHasKey($key, $decoded, "Field '$key' should exist");
            if ($key !== 'reviewed_at') {  // Skip timestamp comparison
                $this->assertEquals($value, $decoded[$key], "Field '$key' should match expected value");
            }
        }
    }
}

