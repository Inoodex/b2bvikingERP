<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SalesQuotation;
use App\Http\Controllers\Backend\SalesQuotationController;

$quote = SalesQuotation::first();
if ($quote) {
    echo "Testing PDF generation for Quote No: " . $quote->quotation_no . "...\n";
    $controller = new SalesQuotationController();
    try {
        $pdfOutput = $controller->pdf($quote);
        echo "SUCCESS! PDF generated successfully without error.\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
    }
}
