<?php

namespace App\Exports;

use App\Models\SalesQuotation;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesQuotationExcelExport implements FromArray, WithCustomStartCell, ShouldAutoSize, WithEvents
{
    protected SalesQuotation $quotation;

    public function __construct(SalesQuotation $quotation)
    {
        $this->quotation = $quotation;
    }

    public function array(): array
    {
        return [];
    }

    public function startCell(): string
    {
        return 'Z100';
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $quotation = $this->quotation->loadMissing(['customer', 'currency', 'tax', 'items.product', 'items.variant.color', 'items.variant.size']);
                $sheet = $event->writer->getDelegate()->getActiveSheet();
                $sheet->setTitle('Order Sheet');

                $currencySymbol = $quotation->currency?->symbol ?? 'kr.';
                $currencyCode = $quotation->currency?->code ?? 'DKK';

                // --- STYLES ---
                $headerTitleStyle = [
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0F172A']],
                ];
                $subHeaderStyle = [
                    'font' => ['bold' => false, 'size' => 10, 'color' => ['rgb' => '64748B']],
                ];
                $metaLabelStyle = [
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '475569']],
                ];
                $metaValueStyle = [
                    'font' => ['bold' => false, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                ];
                $tableHeaderStyle = [
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ];
                $orderQtyHeaderStyle = [
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1D4ED8']]],
                ];
                $orderQtyCellStyle = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1E3A8A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']]],
                ];
                $cellBorder = [
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ];

                // Title Section
                $sheet->setCellValue('A1', 'B2B VIKING - BUYER CATALOG & ORDER SHEET');
                $sheet->getStyle('A1')->applyFromArray($headerTitleStyle);

                $sheet->setCellValue('A2', 'Official Price Quote & B2B Order Form | Copenhagen Tourist Point');
                $sheet->getStyle('A2')->applyFromArray($subHeaderStyle);

                // Meta Info Box
                $sheet->setCellValue('A4', 'Quotation #:');
                $sheet->setCellValue('B4', $quotation->quotation_no);
                $sheet->setCellValue('A5', 'Date:');
                $sheet->setCellValue('B5', $quotation->created_at ? $quotation->created_at->format('d M, Y') : date('d M, Y'));
                $sheet->setCellValue('A6', 'Valid Until:');
                $sheet->setCellValue('B6', $quotation->valid_until ? $quotation->valid_until->format('d M, Y') : 'N/A');

                $sheet->setCellValue('D4', 'Customer / Buyer:');
                $customerName = $quotation->buyer_display_name;
                $sheet->setCellValue('E4', $customerName);
                if ($quotation->is_prospect) {
                    $sheet->setCellValue('D5', 'Phone:');
                    $sheet->setCellValue('E5', $quotation->buyer_phone ?: 'Direct Lead');
                } else {
                    $sheet->setCellValue('D5', 'Email:');
                    $sheet->setCellValue('E5', $quotation->customer?->email ?? 'N/A');
                }
                $sheet->setCellValue('D6', 'Currency / Terms:');
                $sheet->setCellValue('E6', "{$currencyCode} ({$currencySymbol}) | Incoterm: " . ($quotation->incoterm ?? 'EXW'));

                $sheet->getStyle('A4:A6')->applyFromArray($metaLabelStyle);
                $sheet->getStyle('B4:B6')->applyFromArray($metaValueStyle);
                $sheet->getStyle('D4:D6')->applyFromArray($metaLabelStyle);
                $sheet->getStyle('E4:E6')->applyFromArray($metaValueStyle);

                // Table Headers (Row 8)
                $headers = [
                    'A8' => '#',
                    'B8' => 'Product Name',
                    'C8' => 'SKU / Code',
                    'D8' => 'Barcode',
                    'E8' => 'Variant (Color/Size)',
                    'F8' => "Wholesale Price ({$currencySymbol})",
                    'G8' => "Retail MSRP ({$currencySymbol})",
                    'H8' => 'Quoted Qty',
                    'I8' => 'BUYER ORDER QTY (Type Here)',
                ];

                foreach ($headers as $col => $title) {
                    $sheet->setCellValue($col, $title);
                    if ($col === 'I8') {
                        $sheet->getStyle($col)->applyFromArray($orderQtyHeaderStyle);
                    } else {
                        $sheet->getStyle($col)->applyFromArray($tableHeaderStyle);
                    }
                }
                $sheet->getRowDimension(8)->setRowHeight(28);

                // Rows
                $row = 9;
                foreach ($quotation->items as $index => $item) {
                    $product = $item->product;
                    $variant = $item->variant;
                    $variantText = '-';
                    if ($variant) {
                        $vParts = [];
                        if ($variant->color?->name) $vParts[] = 'Color: ' . $variant->color->name;
                        if ($variant->size?->name) $vParts[] = 'Size: ' . $variant->size->name;
                        $variantText = !empty($vParts) ? implode(', ', $vParts) : ($variant->name ?? '-');
                    }

                    $sheet->setCellValue("A{$row}", $index + 1);
                    $sheet->setCellValue("B{$row}", $product?->name ?? 'Product');
                    $sheet->setCellValue("C{$row}", $product?->product_number ?? ('PROD-' . $item->product_id));
                    $sheet->setCellValue("D{$row}", $product?->sku ?? $product?->custom_label ?? '-');
                    $sheet->setCellValue("E{$row}", $variantText);
                    $sheet->setCellValue("F{$row}", number_format((float)$item->unit_price, 2));
                    $sheet->setCellValue("G{$row}", number_format((float)($product?->price ?? 0), 2));
                    $sheet->setCellValue("H{$row}", (float)$item->qty);
                    $sheet->setCellValue("I{$row}", ''); // Blank for buyer to input order quantity

                    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($cellBorder);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Highlight Order Qty column cell for input
                    $sheet->getStyle("I{$row}")->applyFromArray($orderQtyCellStyle);

                    $sheet->getRowDimension($row)->setRowHeight(22);
                    $row++;
                }

                // Summary Note for Buyer at bottom
                $noticeRow = $row + 1;
                $sheet->setCellValue("A{$noticeRow}", "HOW TO ORDER: Please fill in column I ('BUYER ORDER QTY') with your desired quantities and email this sheet back to our sales representative.");
                $sheet->getStyle("A{$noticeRow}")->applyFromArray([
                    'font' => ['italic' => true, 'bold' => true, 'size' => 10, 'color' => ['rgb' => '1D4ED8']],
                ]);

                // Auto size columns
                foreach (range('A', 'I') as $c) {
                    $sheet->getColumnDimension($c)->setAutoSize(true);
                }
            },
        ];
    }
}
