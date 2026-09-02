<?php

namespace Tests\Feature\Accounting;

use App\Models\ChartOfAccount;
use App\Models\Order;
use App\Services\Accounting\JournalEntryService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JournalPostingEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('accounting:seed-coa');
    }

    #[Test]
    public function it_posts_balanced_journal_entry_successfully()
    {
        $order = Order::first() ?? Order::factory()->create();
        $service = new JournalEntryService();

        $lines = [
            ['account_code' => '1020', 'debit' => 500, 'credit' => 0],
            ['account_code' => '1030', 'debit' => 0, 'credit' => 500],
        ];

        $entry = $service->postJournal('Test Payment', $order, $lines);

        $this->assertNotNull($entry);
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $entry->id,
            'debit'            => 500,
        ]);
    }

    #[Test]
    public function it_throws_exception_on_imbalanced_journal_posting()
    {
        $order = Order::first() ?? Order::factory()->create();
        $service = new JournalEntryService();

        $lines = [
            ['account_code' => '1020', 'debit' => 500, 'credit' => 0],
            ['account_code' => '1030', 'debit' => 0, 'credit' => 400], // Imbalance!
        ];

        $this->expectException(Exception::class);
        $service->postJournal('Imbalanced Test', $order, $lines);
    }
}
