<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SalesQuotation;
use App\Http\Controllers\Backend\SalesQuotationController;

$quote = SalesQuotation::latest()->first();
if ($quote) {
    echo "Testing deletion of Sales Quotation ID: " . $quote->id . " (" . $quote->quotation_no . ")...\n";
    $controller = new SalesQuotationController();
    $res = $controller->destroy($quote->id);
    echo "Response Content: " . $res->getContent() . "\n";
}
