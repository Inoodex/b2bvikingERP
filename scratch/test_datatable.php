<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\DataTables\SalesQuotationDataTable;
use Illuminate\Http\Request;

$request = Request::create('/admin/sales-quotations', 'GET', [
    'draw' => 1,
    'start' => 0,
    'length' => 10,
]);
app()->instance('request', $request);

$dataTable = new SalesQuotationDataTable();
try {
    $response = app()->call([$dataTable, 'render'], ['view' => 'backend.sales_quotation.index']);
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        echo "AJAX JSON Response Success: " . substr($response->getContent(), 0, 300) . "\n";
    } else {
        echo "Rendered View Output.\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
