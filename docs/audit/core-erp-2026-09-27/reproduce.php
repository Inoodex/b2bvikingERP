<?php
// Diagnostic reproduction only. Uses synthetic tables in SQLite :memory:.
// Run from repository root: php docs/audit/core-erp-2026-09-27/reproduce.php
use Illuminate\Support\Facades\DB;
$auditRoot = dirname(__DIR__, 3);
$auditEnv = [
 'APP_ENV'=>'testing', 'DB_CONNECTION'=>'sqlite', 'DB_DATABASE'=>':memory:',
 'DB_URL'=>'', 'CACHE_STORE'=>'array', 'SESSION_DRIVER'=>'array',
 'MAIL_MAILER'=>'array', 'QUEUE_CONNECTION'=>'sync',
 'APP_CONFIG_CACHE'=>sys_get_temp_dir().'/b2bviking-audit-no-cache-'.bin2hex(random_bytes(12)),
];
foreach ($auditEnv as $key=>$value) { putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value; }
require $auditRoot.'/vendor/autoload.php';
$app=require $auditRoot.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
 throw new RuntimeException('Audit refused: database is not SQLite :memory:.');
}

DB::unprepared('CREATE TABLE orders (id INTEGER PRIMARY KEY, outlet_id INTEGER, fulfillment_status TEXT, deleted_at TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE order_items (id INTEGER PRIMARY KEY, order_id INTEGER, quantity REAL);
CREATE TABLE delivery_orders (id INTEGER PRIMARY KEY, order_id INTEGER, status TEXT, dispatched_by INTEGER, deleted_at TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE delivery_order_items (id INTEGER PRIMARY KEY, delivery_order_id INTEGER, order_item_id INTEGER, product_id INTEGER, variant_id INTEGER, qty_delivered REAL);
CREATE TABLE inventory_stocks (id INTEGER PRIMARY KEY, product_id INTEGER, variant_id INTEGER, outlet_id INTEGER, bin_id INTEGER, quantity REAL, created_at TEXT, updated_at TEXT);
CREATE TABLE stock_ledgers (id INTEGER PRIMARY KEY, product_id INTEGER, variant_id INTEGER, outlet_id INTEGER, bin_id INTEGER, batch_id INTEGER, reference_type TEXT, reference_id INTEGER, in_qty REAL, out_qty REAL, balance_qty REAL, date TEXT, created_at TEXT, updated_at TEXT);');
DB::table('orders')->insert(['id'=>1,'outlet_id'=>2,'fulfillment_status'=>'unfulfilled']);
DB::table('order_items')->insert(['id'=>1,'order_id'=>1,'quantity'=>10]);
DB::table('delivery_orders')->insert(['id'=>1,'order_id'=>1,'status'=>'pending']);
DB::table('delivery_order_items')->insert(['id'=>1,'delivery_order_id'=>1,'order_item_id'=>1,'product_id'=>1,'variant_id'=>null,'qty_delivered'=>10]);
DB::table('inventory_stocks')->insert([
 ['id'=>1,'product_id'=>1,'variant_id'=>null,'outlet_id'=>1,'bin_id'=>1,'quantity'=>3],
 ['id'=>2,'product_id'=>1,'variant_id'=>null,'outlet_id'=>2,'bin_id'=>2,'quantity'=>20],
]);
(new App\Services\DeliveryOrderService)->dispatchDeliveryOrder(App\Models\DeliveryOrder::findOrFail(1),1);
echo json_encode(['case'=>'dispatch_10_from_outlet_2','stocks_after'=>DB::table('inventory_stocks')->get(['outlet_id','quantity']),'ledger'=>DB::table('stock_ledgers')->get(['outlet_id','out_qty','balance_qty']),'status'=>DB::table('delivery_orders')->value('status')],JSON_PRETTY_PRINT),"\n";
$service=new App\Services\CustomerPaymentService(new App\Services\Accounting\JournalEntryService);
try{$service->recordPayment(['amount'=>1]);}catch(Throwable $e){echo 'public_invoice_payment_call=',get_class($e),' required_args=',(new ReflectionMethod($service,'recordPayment'))->getNumberOfRequiredParameters(),"\n";}

DB::unprepared('ALTER TABLE orders ADD COLUMN user_id INTEGER;
CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, outlet_name TEXT, deleted_at TEXT);
CREATE TABLE products (id INTEGER PRIMARY KEY, purchase_price REAL, deleted_at TEXT);
CREATE TABLE journal_entries (id INTEGER PRIMARY KEY, entry_no TEXT UNIQUE, entry_date TEXT, reference_type TEXT, reference_id INTEGER, narration TEXT, created_by INTEGER, created_at TEXT, updated_at TEXT);
CREATE TABLE journal_entry_lines (id INTEGER PRIMARY KEY, journal_entry_id INTEGER, account_id INTEGER, debit REAL, credit REAL, created_at TEXT, updated_at TEXT);
CREATE TABLE fiscal_years (id INTEGER PRIMARY KEY, name TEXT, is_closed INTEGER, start_date TEXT, end_date TEXT);
CREATE TABLE chart_of_accounts (id INTEGER PRIMARY KEY, account_code TEXT, account_type TEXT);
CREATE TABLE sales_invoices (id INTEGER PRIMARY KEY, order_id INTEGER, invoice_no TEXT, payment_token TEXT, subtotal_amount REAL, discount_amount REAL, tax_amount REAL, total_amount REAL, paid_amount REAL, due_amount REAL, status TEXT, date TEXT, deleted_at TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE goods_receipts (id INTEGER PRIMARY KEY, grn_no TEXT, purchase_id INTEGER, outlet_id INTEGER, qc_status TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE goods_receipt_items (id INTEGER PRIMARY KEY, goods_receipt_id INTEGER, product_id INTEGER, accepted_qty REAL, created_at TEXT, updated_at TEXT);
CREATE TABLE warehouse_bins (id INTEGER PRIMARY KEY, name TEXT, barcode TEXT);
CREATE TABLE stock_batches (id INTEGER PRIMARY KEY, outlet_id INTEGER, bin_id INTEGER, product_id INTEGER, variant_id INTEGER, batch_no TEXT, qty_remaining REAL, unit_cost REAL, received_date TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE payment_transactions (id INTEGER PRIMARY KEY, transaction_no TEXT, gateway TEXT, order_id INTEGER, sales_invoice_id INTEGER, customer_id INTEGER, amount REAL, currency TEXT, external_reference TEXT, status TEXT, payload TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE vendors (id INTEGER PRIMARY KEY, currency_name TEXT, currency_icon TEXT, currency_id INTEGER);');
$results=[];
DB::table('users')->insert(['id'=>1,'name'=>'Synthetic audit user']);
DB::table('orders')->where('id',1)->update(['user_id'=>1]);
DB::table('chart_of_accounts')->insert([['id'=>1,'account_code'=>'1030','account_type'=>'asset'],['id'=>2,'account_code'=>'4010','account_type'=>'revenue'],['id'=>3,'account_code'=>'1020','account_type'=>'asset']]);
DB::table('sales_invoices')->insert(['id'=>1,'order_id'=>1,'invoice_no'=>'AUDIT-1','payment_token'=>'synthetic-token','subtotal_amount'=>100,'discount_amount'=>0,'tax_amount'=>0,'total_amount'=>100,'paid_amount'=>0,'due_amount'=>100,'status'=>'posted','date'=>'2026-09-27']);
$invoice=App\Models\SalesInvoice::findOrFail(1);
$journal=new App\Services\Accounting\JournalEntryService;
$invoiceService=new App\Services\SalesInvoiceService($journal);
$invoiceService->postInvoiceToLedger($invoice,1);
$invoiceService->postInvoiceToLedger($invoice->fresh(),1);
$results['repeat_invoice_post']=['expected_journals'=>1,'actual_journals'=>DB::table('journal_entries')->count(),'status_after'=>$invoice->fresh()->status];
try{$journal->postJournal('imbalance',$invoice,[['account_code'=>'1030','debit'=>100,'credit'=>0]]);$results['imbalance_control']='incorrectly allowed';}catch(Exception $e){$results['imbalance_control']='correctly rejected';}
DB::table('fiscal_years')->insert(['id'=>1,'name'=>'Audit closed','is_closed'=>1,'start_date'=>'2026-01-01','end_date'=>'2026-12-31']);
try{$journal->postJournal('closed period',$invoice,[['account_code'=>'1030','debit'=>100,'credit'=>0],['account_code'=>'4010','debit'=>0,'credit'=>100]],'2026-09-27');$results['fiscal_lock_control']='incorrectly allowed';}catch(Exception $e){$results['fiscal_lock_control']='correctly rejected';}
DB::table('fiscal_years')->delete();
DB::table('products')->insert(['id'=>1,'purchase_price'=>5]);
$grn=App\Models\GoodsReceipt::create(['grn_no'=>'AUDIT-GRN','purchase_id'=>1,'outlet_id'=>1,'qc_status'=>'passed']);
App\Models\GoodsReceiptItem::create(['goods_receipt_id'=>$grn->id,'product_id'=>1,'accepted_qty'=>10]);
$results['grn_created_before_items']=['items_after'=> $grn->items()->count(),'journals_after'=>DB::table('journal_entries')->where('reference_type',$grn->getMorphClass())->where('reference_id',$grn->id)->count()];
DB::table('inventory_stocks')->update(['quantity'=>10]);
DB::table('inventory_stocks')->where('id',2)->update(['outlet_id'=>1]);
DB::table('warehouse_bins')->insert([['id'=>1,'name'=>'A','barcode'=>'A'],['id'=>2,'name'=>'B','barcode'=>'B']]);
DB::table('stock_batches')->insert([
 ['id'=>1,'outlet_id'=>1,'bin_id'=>2,'product_id'=>1,'variant_id'=>null,'batch_no'=>'older-B','qty_remaining'=>10,'unit_cost'=>7,'received_date'=>'2026-01-01'],
 ['id'=>2,'outlet_id'=>1,'bin_id'=>1,'product_id'=>1,'variant_id'=>null,'batch_no'=>'newer-A','qty_remaining'=>10,'unit_cost'=>2,'received_date'=>'2026-01-02'],
]);
$fifo=(new App\Services\Inventory\FifoDepletionService)->depleteStock(1,1,1,null,3,'audit',1);
$results['fifo_bin_selection']=['requested_bin'=>1,'selected_batch_bin'=>$fifo['log'][0]['bin_id'],'actual_cost'=>$fifo['total_cogs'],'expected_cost'=>6,'stocks'=>DB::table('inventory_stocks')->get(['bin_id','quantity']),'batches'=>DB::table('stock_batches')->get(['bin_id','qty_remaining'])];
DB::table('payment_transactions')->insert(['id'=>1,'transaction_no'=>'AUDIT-TXN','gateway'=>'paypal','order_id'=>1,'sales_invoice_id'=>1,'customer_id'=>1,'amount'=>100,'currency'=>'DKK','external_reference'=>'AUDIT-PP','status'=>'pending']);
$gateway=new class extends App\Services\Payment\PayPalService {
 public int $captures=0;
 public function captureOrder(string $orderId):array{$this->captures++;return ['status'=>'COMPLETED','purchase_units'=>[['custom_id'=>'INV:1','payments'=>['captures'=>[['id'=>'AUDIT-CAP','amount'=>['value'=>'100.00','currency_code'=>'DKK']]]]]]];}
};
$controller=new App\Http\Controllers\Frontend\InvoicePaymentController($gateway,new App\Services\CustomerPaymentService($journal));
try{$controller->paypalSuccess(Illuminate\Http\Request::create('/invoices/pay/synthetic-token/paypal/success?token=AUDIT-PP'),'synthetic-token');$paymentError=null;}catch(Throwable $e){$paymentError=get_class($e);}
$results['invoice_callback_with_simulated_gateway']=['simulated_capture_calls'=>$gateway->captures,'exception'=>$paymentError,'transaction_status_after'=>DB::table('payment_transactions')->where('id',1)->value('status'),'invoice_due_after'=>$invoice->fresh()->due_amount];
try{$migration=require $auditRoot . '/database/migrations/2026_07_27_041317_refactor_vendors_currency_columns.php';$migration->up();$results['historical_vendor_migration']='unexpectedly succeeded';}catch(Throwable $e){$results['historical_vendor_migration']=['exception'=>get_class($e),'missing_deleted_at'=>str_contains($e->getMessage(),'vendors.deleted_at')];}
echo json_encode($results,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";
