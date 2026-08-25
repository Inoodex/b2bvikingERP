<?php

namespace Tests\Unit\Inventory;

use App\Services\Inventory\BarcodeGeneratorService;
use PHPUnit\Framework\TestCase;

class BarcodeGeneratorServiceTest extends TestCase
{
    public function test_it_generates_correct_barcode_format(): void
    {
        $service = new BarcodeGeneratorService();
        $barcode = $service->generateBinBarcode(10, 5, 2);
        
        $this->assertEquals('BIN-10-5-2', $barcode);
    }
}
