<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use App\Models\Order;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class ApprovalTestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure Roles exist
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // 2. Ensure a Manager User exists
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Test Manager',
                'password' => Hash::make('password'),
                'status' => 1
            ]
        );
        if (!$manager->hasRole('Manager')) {
            $manager->assignRole('Manager');
        }

        // 3. Create a Test Workflow
        $workflow = ApprovalWorkflow::firstOrCreate(
            ['name' => 'High Value Orders (> 5000)', 'model_type' => 'App\Models\Order'],
            ['document_type' => 'order', 'min_amount' => 5000, 'max_amount' => null, 'status' => 1]
        );

        // Clean existing steps for this workflow to prevent duplicates
        ApprovalStep::where('approval_workflow_id', $workflow->id)->delete();

        // 4. Create Workflow Steps (Step 1: Manager, Step 2: Admin)
        ApprovalStep::create([
            'approval_workflow_id' => $workflow->id,
            'step_order' => 1,
            'approver_role_id' => $managerRole->id
        ]);

        ApprovalStep::create([
            'approval_workflow_id' => $workflow->id,
            'step_order' => 2,
            'approver_role_id' => $adminRole->id
        ]);

        // 5. Create a Mock Order
        $order = Order::create([
            'order_no' => 'ORD-TEST-' . rand(1000, 9999),
            'user_id' => 1, // assuming user 1 is admin
            'status' => 'pending',
            'shipping_method' => 'test_seed',
            'billing_name' => 'Test User',
            'billing_phone' => '01234567890',
            'billing_email' => 'test@example.com',
            'billing_address' => 'Test Address',
            'subtotal_amount' => 6000,
            'tax_amount' => 0,
            'total_amount' => 6000,
            'paid_amount' => 0,
            'due_amount' => 6000,
            'payment_status' => 'pending',
            'placed_at' => now(),
        ]);

        // 6. Submit the order to the Approval Service
        $approvalService = app(ApprovalService::class);
        $approvalService->submitForApproval($order, $order->total_amount);

        echo "Test data seeded successfully! Order ID: {$order->id}\n";
    }
}
