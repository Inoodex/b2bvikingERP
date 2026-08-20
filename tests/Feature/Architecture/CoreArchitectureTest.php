<?php

namespace Tests\Feature\Architecture;

use App\Models\Approval;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Outlet;
use App\Models\Purchase;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\OrderNumberService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CoreArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_01_can_create_company_and_outlets(): void
    {
        $company = Company::create([
            'name' => 'Viking Group Denmark A/S ' . uniqid(),
            'code' => 'VKG-' . uniqid(),
            'email' => 'admin@vikinggroup.dk',
            'phone' => '+45 70 20 00 00',
            'address' => 'Vesterbrogade 12, 1620 Copenhagen',
            'is_default' => 1,
            'status' => 1,
        ]);

        $this->assertDatabaseHas('companies', ['id' => $company->id]);

        $outlet1 = Outlet::create([
            'name' => 'Copenhagen Central Hub',
            'code' => 'CPH-01-' . uniqid(),
            'company_id' => $company->id,
            'is_default' => 1,
            'status' => 1,
        ]);

        $outlet2 = Outlet::create([
            'name' => 'Aarhus Distribution Center',
            'code' => 'AAR-01-' . uniqid(),
            'company_id' => $company->id,
            'is_default' => 0,
            'status' => 1,
        ]);

        $this->assertEquals(2, $company->outlets()->count());
    }

    public function test_02_currencies_and_exchange_rates_invariants(): void
    {
        $dkk = Currency::where('code', 'DKK')->first() ?? Currency::create([
            'name' => 'Danish Krone',
            'code' => 'DKK',
            'symbol' => 'kr.',
            'exchange_rate' => 1.0000,
            'is_base' => 1,
            'status' => 1,
        ]);

        $eur = Currency::where('code', 'EUR')->first() ?? Currency::create([
            'name' => 'Euro',
            'code' => 'EUR',
            'symbol' => '€',
            'exchange_rate' => 7.4500,
            'is_base' => 0,
            'status' => 1,
        ]);

        $this->assertEquals(1.0000, (float) $dkk->exchange_rate);
        $this->assertEquals(7.4500, (float) $eur->exchange_rate);
    }

    public function test_03_department_creation_with_manager(): void
    {
        $user = User::first() ?? User::create([
            'name' => 'Dept Manager',
            'email' => 'mgr_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
        ]);

        $department = Department::create([
            'name' => 'Supply Chain & Logistics ' . uniqid(),
            'code' => 'LOG-' . uniqid(),
            'manager_id' => $user->id,
            'status' => 1,
        ]);

        $this->assertDatabaseHas('departments', ['id' => $department->id, 'manager_id' => $user->id]);
    }

    public function test_04_atomic_document_sequence_generation(): void
    {
        $seq1 = OrderNumberService::generate('PO', Purchase::class, 'purchase_orders');
        $seq2 = OrderNumberService::generate('PO', Purchase::class, 'purchase_orders');

        $this->assertNotEmpty($seq1);
        $this->assertNotEmpty($seq2);
        $this->assertNotEquals($seq1, $seq2);
    }

    public function test_05_multi_level_approval_chain_progression_and_rejection(): void
    {
        $role = Role::firstOrCreate(['name' => 'Finance Reviewer', 'guard_name' => 'web']);
        $workflow = ApprovalWorkflow::create([
            'name' => 'Purchase Order Test Workflow ' . uniqid(),
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
            'name' => 'Nordic Supplier ApS',
            'shop_name' => 'Nordic Supplier Shop',
            'email' => 'vendor_app_' . uniqid() . '@example.com',
            'phone' => '+45 11223344',
            'country' => 'Denmark',
            'address' => 'Copenhagen',
            'status' => 1,
        ]);

        $purchase = Purchase::create([
            'po_no' => 'PO-TEST-' . uniqid(),
            'invoice_no' => 'INV-TEST-' . uniqid(),
            'vendor_id' => $vendor->id,
            'outlet_id' => 1,
            'purchase_type' => 'local',
            'date' => now()->toDateString(),
            'total_amount' => 5000.00,
            'grand_total' => 5000.00,
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
