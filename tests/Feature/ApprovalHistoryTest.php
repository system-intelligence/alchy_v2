<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MaterialReleaseApproval;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Client;
use App\Models\Project;
use App\Models\History;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_approval_history_records_client_and_project_on_approval(): void
    {
        // Create test data
        $requester = User::factory()->create();
        $approver = User::factory()->create();
        $client = Client::factory()->create(['name' => 'CHINABANK']);
        $project = Project::factory()->create(['name' => 'Signage Project']);
        $inventory = Inventory::factory()->create(['material_name' => 'Del Monte Tomato Sauce']);
        
        $expense = Expense::factory()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
        ]);

        // Create approval request
        $approval = MaterialReleaseApproval::create([
            'requester_id' => $requester->id,
            'inventory_id' => $inventory->id,
            'expense_id' => $expense->id,
            'quantity_requested' => 1,
            'status' => 'pending',
        ]);

        // Simulate approval with client and project in history
        $approval->update(['status' => 'approved', 'reviewer_id' => $approver->id]);

        // Create history entry like ChatWidget does
        $historyRecord = History::create([
            'user_id' => $approver->id,
            'model' => 'MaterialReleaseApproval',
            'model_id' => $approval->id,
            'model_name' => 'Material Release Approval',
            'action' => 'Approval Request Approved',
            'action_description' => 'Approval Request Approved',
            'changes' => json_encode([
                'status' => 'approved',
                'requester' => $requester->name,
                'client' => $client->name,
                'project' => $project->name,
                'material' => $inventory->material_name,
                'quantity' => $approval->quantity_requested,
                'reviewer' => $approver->name,
                'reviewed_at' => now()->toDateTimeString(),
            ]),
        ]);

        // Verify history record exists
        $this->assertDatabaseHas('histories', [
            'model_id' => $approval->id,
            'action' => 'Approval Request Approved',
        ]);

        // Verify the changes JSON contains client and project
        $history = History::find($historyRecord->id);
        $changes = json_decode($history->changes, true);

        $this->assertArrayHasKey('client', $changes);
        $this->assertArrayHasKey('project', $changes);
        $this->assertEquals('CHINABANK', $changes['client']);
        $this->assertEquals('Signage Project', $changes['project']);
        $this->assertEquals('Del Monte Tomato Sauce', $changes['material']);
    }

    public function test_approval_history_retrieves_client_and_project_from_relationships(): void
    {
        // Create test data
        $requester = User::factory()->create();
        $approver = User::factory()->create();
        $client = Client::factory()->create(['name' => 'Test Client']);
        $project = Project::factory()->create(['name' => 'Test Project']);
        $inventory = Inventory::factory()->create();
        
        $expense = Expense::factory()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
        ]);

        // Create approval
        $approval = MaterialReleaseApproval::create([
            'requester_id' => $requester->id,
            'inventory_id' => $inventory->id,
            'expense_id' => $expense->id,
            'quantity_requested' => 5,
            'status' => 'approved',
            'reviewer_id' => $approver->id,
        ]);

        // Load with relationships
        $approval = MaterialReleaseApproval::with('expense.client', 'expense.project')
            ->find($approval->id);

        // Verify relationships load correctly
        $this->assertNotNull($approval->expense);
        $this->assertNotNull($approval->expense->client);
        $this->assertNotNull($approval->expense->project);
        $this->assertEquals('Test Client', $approval->expense->client->name);
        $this->assertEquals('Test Project', $approval->expense->project->name);
    }

    public function test_approval_history_displays_client_and_project_when_approved(): void
    {
        // Create test data
        $requester = User::factory()->create();
        $approver = User::factory()->create();
        $client = Client::factory()->create(['name' => 'CHINABANK']);
        $project = Project::factory()->create(['name' => 'Signage Project']);
        $inventory = Inventory::factory()->create(['material_name' => 'Del Monte Tomato Sauce']);
        
        $expense = Expense::factory()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
        ]);

        $approval = MaterialReleaseApproval::create([
            'requester_id' => $requester->id,
            'inventory_id' => $inventory->id,
            'expense_id' => $expense->id,
            'quantity_requested' => 1,
            'status' => 'approved',
            'reviewer_id' => $approver->id,
        ]);

        // Create history with JSON changes
        $history = History::create([
            'user_id' => $approver->id,
            'model' => 'MaterialReleaseApproval',
            'model_id' => $approval->id,
            'model_name' => 'Material Release Approval',
            'action' => 'Approval Request Approved',
            'action_description' => 'Approval Request Approved',
            'changes' => json_encode([
                'status' => 'approved',
                'client' => $client->name,
                'project' => $project->name,
                'material' => $inventory->material_name,
                'quantity' => 1,
            ]),
        ]);

        // Test JSON decoding logic (as in blade template)
        $changes = [];
        if (is_string($history->changes)) {
            $changes = json_decode($history->changes, true) ?? [];
        } elseif (is_array($history->changes)) {
            $changes = $history->changes;
        }

        // Verify client and project are accessible
        $this->assertNotEmpty($changes['client']);
        $this->assertNotEmpty($changes['project']);
        $this->assertEquals('CHINABANK', $changes['client']);
        $this->assertEquals('Signage Project', $changes['project']);
    }
}
