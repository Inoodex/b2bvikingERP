<?php

namespace Tests\Feature\Architecture;

use App\Models\Approval;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\ApprovalWorkflow\ApprovalWorkflowService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_create_multi_level_approval_workflow(): void
    {
        $service = app(ApprovalWorkflowService::class);
        $role = Role::firstOrCreate(['name' => 'Department Head', 'guard_name' => 'web']);

        $workflow = $service->createWorkflow([
            'name' => 'Purchase Order High Value Approval',
            'model_type' => Purchase::class,
            'min_amount' => 1000,
            'max_amount' => 50000,
            'status' => true,
            'steps' => [
                ['step_name' => 'Dept Head Review', 'approver_role_id' => $role->id],
            ]
        ]);

        $this->assertDatabaseHas('approval_workflows', [
            'id' => $workflow->id,
            'name' => 'Purchase Order High Value Approval',
            'min_amount' => 1000,
        ]);

        $this->assertDatabaseHas('approval_steps', [
            'approval_workflow_id' => $workflow->id,
            'step_name' => 'Dept Head Review',
            'step_order' => 1,
        ]);
    }

    public function test_submitting_model_creates_first_step_pending_approval(): void
    {
        $role = Role::firstOrCreate(['name' => 'Finance Reviewer', 'guard_name' => 'web']);
        $workflow = ApprovalWorkflow::create([
            'name' => 'Purchase Order Test Workflow',
            'document_type' => 'purchase',
            'model_type' => Purchase::class,
            'min_amount' => 0,
            'max_amount' => 100000,
            'status' => true,
        ]);

        $step = ApprovalStep::create([
            'approval_workflow_id' => $workflow->id,
            'step_name' => 'Finance Step',
            'step_order' => 1,
            'approver_role_id' => $role->id,
        ]);

        $vendor = \App\Models\Vendor::first() ?? \App\Models\Vendor::create([
            'name' => 'Approval Vendor',
            'shop_name' => 'Approval Shop',
            'email' => 'app_v_' . uniqid() . '@example.com',
            'phone' => '+45 11223344',
            'country' => 'Denmark',
            'address' => 'Copenhagen',
            'status' => 1,
        ]);

        $purchase = Purchase::first() ?? Purchase::create([
            'po_no' => 'PO-TEST-' . uniqid(),
            'invoice_no' => 'INV-TEST-' . uniqid(),
            'vendor_id' => $vendor->id,
            'purchase_type' => 'local',
            'date' => now()->toDateString(),
            'total_amount' => 5000.00,
            'milestone_status' => 'draft',
        ]);

        $approvalService = app(ApprovalService::class);
        $result = $approvalService->submitForApproval($purchase, 5000.00);

        $this->assertTrue($result);
        $this->assertEquals('pending', $purchase->fresh()->approval_status);

        $this->assertDatabaseHas('approvals', [
            'approvable_type' => Purchase::class,
            'approvable_id' => $purchase->id,
            'approval_step_id' => $step->id,
            'status' => 'pending',
        ]);
    }
}
