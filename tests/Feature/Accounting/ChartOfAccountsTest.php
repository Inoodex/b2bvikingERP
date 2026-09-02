<?php

namespace Tests\Feature\Accounting;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChartOfAccountsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);
    }

    #[Test]
    public function it_can_display_chart_of_accounts_index()
    {
        ChartOfAccount::firstOrCreate([
            'account_code'   => '1010',
        ], [
            'account_name'   => 'Cash on Hand',
            'account_type'   => 'asset',
            'normal_balance' => 'debit',
            'is_group'       => false,
            'is_active'      => true,
        ]);

        $response = $this->get(route('admin.chart-of-accounts.index'));

        $response->assertStatus(200);
        $response->assertSee('Chart of Accounts (COA)');
    }

    #[Test]
    public function it_can_create_new_account_head()
    {
        $code = '504' . rand(10, 99);

        $response = $this->post(route('admin.chart-of-accounts.store'), [
            'account_code'   => $code,
            'account_name'   => 'Utility Expenses',
            'account_type'   => 'expense',
            'normal_balance' => 'debit',
            'is_group'       => 0,
        ]);

        $response->assertRedirect(route('admin.chart-of-accounts.index'));
        $this->assertDatabaseHas('chart_of_accounts', [
            'account_code' => $code,
            'account_name' => 'Utility Expenses',
        ]);
    }
}
