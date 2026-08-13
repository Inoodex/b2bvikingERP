<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "SQ Next: " . App\Models\DocumentSequence::generateNext('SalesQuotation') . "\n";
echo "SO Next: " . App\Models\DocumentSequence::generateNext('SalesOrder') . "\n";
echo "INV Next: " . App\Models\DocumentSequence::generateNext('SalesInvoice') . "\n";
