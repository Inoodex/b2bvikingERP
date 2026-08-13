<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['sales_quotations', 'sales_invoices', 'delivery_orders', 'sales_returns', 'users'];
foreach ($tables as $t) {
    echo "=== $t ===\n";
    $cols = Illuminate\Support\Facades\Schema::getColumnListing($t);
    echo implode(', ', $cols) . "\n\n";
}
