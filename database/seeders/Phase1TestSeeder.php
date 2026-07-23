<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\Company;
use App\Models\Department;
use App\Models\Outlet;
use App\Models\User;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class Phase1TestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Base & Multi Currencies
        $baseCurrency = Currency::updateOrCreate(
            ['code' => 'DKK'],
            [
                'name' => 'Danish Krone',
                'symbol' => 'kr.',
                'exchange_rate' => 1.0,
                'is_base' => true,
                'status' => true,
            ]
        );

        Currency::updateOrCreate(
            ['code' => 'EUR'],
            [
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate' => 0.13,
                'is_base' => false,
                'status' => true,
            ]
        );

        Currency::updateOrCreate(
            ['code' => 'USD'],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate' => 0.14,
                'is_base' => false,
                'status' => true,
            ]
        );

        // 2. Parent Company: Copenhagen Tourist Point A/S
        $mainCompany = Company::updateOrCreate(
            ['code' => 'CTP-01'],
            [
                'name' => 'Copenhagen Tourist Point A/S',
                'email' => 'info@b2bviking.com',
                'phone' => '+45 33 12 34 56',
                'vat_number' => 'DK12345678',
                'address' => 'Nyhavn 17, 1051 Copenhagen, Denmark',
                'currency_id' => $baseCurrency->id,
                'base_currency_id' => $baseCurrency->id,
                'status' => true,
            ]
        );

        Company::updateOrCreate(
            ['code' => 'VRL-02'],
            [
                'name' => 'Viking Retail Group',
                'email' => 'contact@vikingretail.dk',
                'phone' => '+45 33 98 76 54',
                'vat_number' => 'DK87654321',
                'address' => 'Strøget 42, 1160 Copenhagen, Denmark',
                'currency_id' => $baseCurrency->id,
                'base_currency_id' => $baseCurrency->id,
                'status' => true,
            ]
        );

        // Remove duplicate test warehouse if present
        Outlet::where('code', 'WH-CENTRAL')->delete();

        // 3. Central Warehouse
        $centralWH = Outlet::updateOrCreate(
            ['code' => 'WH-01'],
            [
                'company_id' => $mainCompany->id,
                'name' => 'Central Warehouse Copenhagen',
                'type' => 'warehouse',
                'phone' => '+45 33 11 22 33',
                'email' => 'wh@b2bviking.com',
                'address' => 'Havneholmen 29, Copenhagen',
                'status' => true,
            ]
        );

        // 4. Map existing Outlet Users to Outlets in `outlets` table under Copenhagen Tourist Point A/S (excluding test names like Shahadat)
        $outletUsers = User::whereNotNull('outlet_name')
            ->where('outlet_name', '!=', '')
            ->where('outlet_name', 'not like', '%test%')
            ->where('name', 'not like', '%Shahadat%')
            ->get();

        // Delete any outdated test outlets
        Outlet::where('code', 'like', 'OUT-%')->delete();

        foreach ($outletUsers as $index => $u) {
            $code = 'OUT-' . Str::padLeft($index + 1, 2, '0');
            $outletName = $u->outlet_name ? ($u->name . ' (' . $u->outlet_name . ')') : $u->name;

            $outlet = Outlet::create([
                'code' => $code,
                'company_id' => $mainCompany->id,
                'name' => $outletName,
                'type' => 'store',
                'phone' => $u->phone ?? '+45 33 00 ' . sprintf('%02d', $index + 1),
                'email' => $u->email,
                'address' => 'Copenhagen Store Location #' . ($index + 1),
                'manager_id' => $u->id,
                'status' => true,
            ]);

            // Link user to created outlet
            $u->update([
                'outlet_id' => $outlet->id,
            ]);
        }

        // 5. Approval Workflows & Steps
        $workflow = ApprovalWorkflow::updateOrCreate(
            ['document_type' => 'product_request'],
            [
                'name' => 'Requisition (SR/PR) 2-Step Approval',
                'is_active' => true,
            ]
        );

        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $adminUser = User::where('email', 'admin@gmail.com')->first();

        if ($workflow && $adminUser) {
            ApprovalStep::updateOrCreate(
                [
                    'approval_workflow_id' => $workflow->id,
                    'step_order' => 1,
                ],
                [
                    'step_label' => 'Department Manager Review',
                    'approver_role_id' => $managerRole->id,
                    'approver_user_id' => $adminUser->id,
                ]
            );

            ApprovalStep::updateOrCreate(
                [
                    'approval_workflow_id' => $workflow->id,
                    'step_order' => 2,
                ],
                [
                    'step_label' => 'Finance Final Sign-off',
                    'approver_role_id' => $managerRole->id,
                    'approver_user_id' => $adminUser->id,
                ]
            );
        }
    }
}
