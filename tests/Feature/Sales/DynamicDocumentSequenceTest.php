<?php

namespace Tests\Feature\Sales;

use App\Models\DocumentSequence;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DynamicDocumentSequenceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_web_order_sequence_generates_ord_prefix(): void
    {
        $customerRole = Role::findOrCreate('User');
        $customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'name' => 'Web Customer ' . uniqid(),
            'email' => 'customer_' . uniqid() . '@portal.test',
        ]);
        $customer->assignRole('User');

        $orderNo = DocumentSequence::generateForOrder($customer);
        $this->assertStringStartsWith('ORD-', $orderNo);
        $this->assertMatchesRegularExpression('/^ORD-\d{6}-\d{4}$/', $orderNo);
    }

    public function test_outlet_order_sequence_generates_ds_prefix(): void
    {
        $outletRole = Role::findOrCreate('Outlet User');
        $outletUser = User::factory()->create([
            'role_id' => $outletRole->id,
            'name' => 'Outlet Staff ' . uniqid(),
            'email' => 'outlet_' . uniqid() . '@pos.test',
        ]);
        $outletUser->assignRole('Outlet User');

        $orderNo = DocumentSequence::generateForOrder($outletUser);
        $this->assertStringStartsWith('DS-', $orderNo);
        $this->assertMatchesRegularExpression('/^DS-\d{6}-\d{4}$/', $orderNo);
    }

    public function test_sales_order_sequence_generates_so_prefix(): void
    {
        $soNo = DocumentSequence::generateNext('SalesOrder');
        $this->assertStringStartsWith('SO-', $soNo);
        $this->assertMatchesRegularExpression('/^SO-\d{6}-\d{4}$/', $soNo);
    }

    public function test_sales_quotation_sequence_generates_sq_prefix(): void
    {
        $sqNo = DocumentSequence::generateNext('SalesQuotation');
        $this->assertStringStartsWith('SQ-', $sqNo);
        $this->assertMatchesRegularExpression('/^SQ-\d{6}-\d{4}$/', $sqNo);
    }
}
