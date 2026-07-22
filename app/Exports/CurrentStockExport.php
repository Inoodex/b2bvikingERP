<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\GeneralSetting;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CurrentStockExport implements FromArray, WithCustomStartCell, WithEvents
{
    protected $categoryId;
    protected $stockStatus;
    protected $vendorId;

    // Columns: A=SL  B=Vendor  C=Product Name  D=Item Code  E=Photo  F=Qty  G=Unit Price  H=Total
    private const COLS     = ['A','B','C','D','E','F','G','H'];
    private const LAST_COL = 'H';

    public function __construct($categoryId = null, $stockStatus = null, $vendorId = null)
    {
        $this->categoryId  = $categoryId;
        $this->stockStatus = $stockStatus;
        $this->vendorId    = $vendorId;
    }

    public function array(): array  { return []; }
    public function startCell(): string { return 'A1'; }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                set_time_limit(0);
                ini_set('memory_limit', '-1');

                $settings  = GeneralSetting::first();
                $currency  = $settings->currency_icon ?? 'Kr.';
                $sheet     = $event->writer->getDelegate()->getActiveSheet();
                $sheet->setTitle('Current Stock');

                // ── 1. Fetch products ───────────────────────────────────────
                $query = Product::with(['vendor', 'category'])
                    ->withSum('inventoryStocks', 'quantity')
                    ->where('status', 1);

                if ($this->categoryId)  $query->where('category_id', $this->categoryId);
                if ($this->vendorId)    $query->where('vendor_id',   $this->vendorId);
                if ($this->stockStatus === 'in_stock') {
                    $query->having('inventory_stocks_sum_quantity', '>', 0);
                } elseif ($this->stockStatus === 'out_of_stock') {
                    $query->havingRaw('inventory_stocks_sum_quantity <= 0 OR inventory_stocks_sum_quantity IS NULL');
                }

                // Vendor products first, no-vendor products last
                $products = $query
                    ->orderByRaw('CASE WHEN vendor_id IS NULL THEN 1 ELSE 0 END ASC')
                    ->orderBy('vendor_id', 'asc')
                    ->orderBy('id', 'desc')
                    ->get();

                // ── 2. Column widths ────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(6);   // SL
                $sheet->getColumnDimension('B')->setWidth(28);  // Vendor / Logo area
                $sheet->getColumnDimension('C')->setWidth(32);  // Product Name / Info area
                $sheet->getColumnDimension('D')->setWidth(15);  // Item Code
                $sheet->getColumnDimension('E')->setWidth(13);  // Photo
                $sheet->getColumnDimension('F')->setWidth(9);   // Qty
                $sheet->getColumnDimension('G')->setWidth(15);  // Buying Price
                $sheet->getColumnDimension('H')->setWidth(20);  // Total Buying Price

                $row = 1;

                // ── 3. Company header — logo + info in 3 rows ─────────────
                // Merge B1:B3 so logo spans all 3 rows in column B
                $sheet->mergeCells('B1:B3');
                $sheet->getRowDimension(1)->setRowHeight(36);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(16);

                $logoPath     = public_path($settings->site_logo ?? 'uploads/logo.png');
                $fallbackLogo = public_path('uploads/logo.png');
                $logo         = file_exists($logoPath) ? $logoPath : (file_exists($fallbackLogo) ? $fallbackLogo : null);

                if ($logo) {
                    $drawing = new Drawing();
                    $drawing->setPath($logo);
                    $drawing->setHeight(68); // spans rows 1-3
                    $drawing->setCoordinates('B1'); // Logo in column B
                    $drawing->setOffsetX(4)->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                }

                // Row 1 — Site Name (big, bold) in column C to H
                $sheet->mergeCells('C1:' . self::LAST_COL . '1');
                $sheet->setCellValue('C1', $settings->site_name ?? 'Inventory System');
                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('C1')->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Row 2 — Email
                $sheet->mergeCells('C2:' . self::LAST_COL . '2');
                $sheet->setCellValue('C2', $settings->contact_email ?? '');
                $sheet->getStyle('C2')->getFont()->setSize(10);
                $sheet->getStyle('C2')->getFont()->getColor()->setARGB('FF2E75B6');
                $sheet->getStyle('C2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Row 3 — Address
                $sheet->mergeCells('C3:' . self::LAST_COL . '3');
                $sheet->setCellValue('C3', $settings->address ?? '');
                $sheet->getStyle('C3')->getFont()->setSize(9);
                $sheet->getStyle('C3')->getFont()->getColor()->setARGB('FF888888');
                $sheet->getStyle('C3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Thin bottom border under header section
                $sheet->getStyle('A1:' . self::LAST_COL . '3')->getBorders()
                    ->getBottom()->setBorderStyle(Border::BORDER_NONE);

                $row = 5; // row 4 blank, row 5 = report title

                // ── 4. Report title ─────────────────────────────────────────
                $sheet->mergeCells("A{$row}:" . self::LAST_COL . "{$row}");
                $sheet->setCellValue("A{$row}", 'CURRENT STOCK REPORT');
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(34);
                $row += 2; // skip one blank row, then column headers

                // ── 5. Column headers ───────────────────────────────────────
                $headerRow  = $row;
                $headerStyle = [
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => Alignment::VERTICAL_CENTER, 'wrapText' => false],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1A5FA3']]],
                ];
                $headers = ['SL', 'Company / Vendor', 'Product Name', 'Item Code', 'Photo', 'Qty', 'Buying Price', 'Total Buying Price'];
                foreach (self::COLS as $i => $col) {
                    $sheet->setCellValue($col . $row, $headers[$i]);
                    $sheet->getStyle($col . $row)->applyFromArray($headerStyle);
                }
                $sheet->getRowDimension($row)->setRowHeight(22);
                $row++;

                // ── 6. Data rows ────────────────────────────────────────────
                $currentVendorId = 'UNMATCHED_INIT';
                $sl    = 1;
                $isEven = false;

                foreach ($products as $product) {
                    $vendorId   = $product->vendor_id;
                    $vendorName = $product->vendor?->shop_name ?? null;

                    // ── Vendor group separator ──────────────────────────────
                    if ($currentVendorId !== $vendorId) {
                        $currentVendorId = $vendorId;
                        $isEven = false;

                        $groupLabel = $vendorName ? '  ' . $vendorName : '  — No Vendor';
                        $sheet->mergeCells("A{$row}:" . self::LAST_COL . "{$row}");
                        $sheet->setCellValue("A{$row}", $groupLabel);
                        $sheet->getStyle("A{$row}")->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $vendorName ? 'D6E4F0' : 'F5F5F5']],
                            'font'      => ['bold' => true, 'size' => 10,
                                            'color' => ['rgb' => $vendorName ? '1F3864' : '888888']],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'C8D8EA']]],
                        ]);
                        $sheet->getRowDimension($row)->setRowHeight(20);
                        $row++;
                    }

                    // ── Row background ──────────────────────────────────────
                    $bgRgb = $isEven ? 'F4F8FC' : 'FFFFFF';

                    $qty         = $product->inventory_stocks_sum_quantity ?? 0;
                    $buyingPrice = $product->purchase_price ?? 0;
                    $total       = $qty * $buyingPrice;
                    $itemCode    = $product->sku ?: ($product->product_number ?: '—');

                    // A – SL
                    $sheet->setCellValue("A{$row}", $sl++);
                    $sheet->getStyle("A{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // B – Vendor
                    $sheet->setCellValue("B{$row}", $vendorName ?? '—');
                    $sheet->getStyle("B{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    // C – Product Name
                    $sheet->setCellValue("C{$row}", $product->name);
                    $sheet->getStyle("C{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    // D – Item Code
                    $sheet->setCellValue("D{$row}", $itemCode);
                    $sheet->getStyle("D{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // E – Photo (embed)
                    $hasImage = false;
                    if ($product->thumb_image) {
                        $fp1 = public_path('storage/' . ltrim($product->thumb_image, '/'));
                        $fp2 = storage_path('app/public/' . ltrim($product->thumb_image, '/'));
                        $fp  = file_exists($fp1) ? $fp1 : (file_exists($fp2) ? $fp2 : null);
                        if ($fp) {
                            $hasImage = $this->embedThumb($sheet, $fp, "E{$row}", 40);
                        }
                    }
                    if (!$hasImage) {
                        $sheet->setCellValue("E{$row}", '—');
                        $sheet->getStyle("E{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }

                    // F – Qty
                    $sheet->setCellValue("F{$row}", $qty);
                    $sheet->getStyle("F{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("F{$row}")->getFont()->getColor()
                        ->setARGB($qty > 0 ? 'FF1A7431' : 'FFDC3545');
                    $sheet->getStyle("F{$row}")->getFont()->setBold($qty == 0);

                    // G – Buying Price
                    $sheet->setCellValue("G{$row}", $currency . number_format($buyingPrice, 2));
                    $sheet->getStyle("G{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // H – Total Buying Price
                    $sheet->setCellValue("H{$row}", $currency . number_format($total, 2));
                    $sheet->getStyle("H{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("H{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Apply background + thin border to all cells in this row
                    foreach (self::COLS as $col) {
                        $cell = $col . $row;
                        $sheet->getStyle($cell)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($bgRgb);
                        $sheet->getStyle($cell)->getBorders()->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->getColor()->setRGB('DEE2E6');
                    }

                    $sheet->getRowDimension($row)->setRowHeight($hasImage ? 46 : 18);
                    $isEven = !$isEven;
                    $row++;
                }

                // done — no grand total, no freeze
            },
        ];
    }

    private function embedThumb($sheet, string $path, string $coord, int $height = 40): bool
    {
        try {
            $dwg = new Drawing();
            $dwg->setPath($path);
            $dwg->setHeight($height);
            $dwg->setCoordinates($coord);
            $dwg->setOffsetX(3)->setOffsetY(3);
            $dwg->setWorksheet($sheet);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
