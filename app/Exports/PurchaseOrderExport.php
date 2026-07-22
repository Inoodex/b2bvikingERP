<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class PurchaseOrderExport implements FromArray, WithCustomStartCell, ShouldAutoSize, WithEvents
{
    protected int $purchaseId;
    private array $darkHeader = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ];
    private array $sectionHeader = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
        'font' => ['bold' => true, 'color' => ['rgb' => '1F3864'], 'size' => 11],
    ];
    private array $tableHeader = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];
    private array $cellBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];
    private array $summaryLabel = [
        'font' => ['bold' => true, 'size' => 11],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];
    private array $summaryValue = [
        'font' => ['bold' => true, 'size' => 11],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];

    public function __construct(int $purchaseId)
    {
        $this->purchaseId = $purchaseId;
    }

    public function array(): array { return []; }
    public function startCell(): string { return 'Z100'; }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $purchase = Purchase::with(['vendor', 'user', 'details.product'])
                    ->findOrFail($this->purchaseId);
                $vendor = $purchase->vendor;
                $settings = \App\Models\GeneralSetting::first();
                $currency = $settings->currency_icon ?? 'Kr.';
                $sheet = $event->writer->getDelegate()->getActiveSheet();
                $row = 1;

                // ── HEADER BAR ──
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->setCellValue("A{$row}", 'PURCHASE ORDER');
                $sheet->getStyle("A{$row}")->applyFromArray($this->darkHeader);
                $sheet->getStyle("A{$row}")->getFont()->setSize(14);
                $sheet->getRowDimension($row)->setRowHeight(36);
                $row += 2;

                // ── INFO SECTION ──
                $infoRow = $row;
                // Left
                $sheet->setCellValue("A{$infoRow}", 'INVOICE INFORMATION');
                $sheet->getStyle("A{$infoRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$infoRow}:C{$infoRow}");
                $infoRow++;
                $leftInfo = [
                    ['Invoice No:', $purchase->invoice_no],
                    ['Date:', $purchase->date],
                    ['Created By:', $purchase->user?->name ?? 'System'],
                ];
                foreach ($leftInfo as $d) {
                    $sheet->setCellValue("A{$infoRow}", $d[0]);
                    $sheet->getStyle("A{$infoRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$infoRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->mergeCells("B{$infoRow}:C{$infoRow}");
                    $sheet->setCellValue("B{$infoRow}", $d[1]);
                    $infoRow++;
                }

                // Right — same row start
                $rightRow = $row;
                $sheet->setCellValue("D{$rightRow}", 'SHIPPING & STATUS');
                $sheet->getStyle("D{$rightRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("D{$rightRow}:F{$rightRow}");
                $rightRow++;
                $rightInfo = [
                    ['Shipping:', $purchase->shipping_method ?? 'N/A'],
                    ['Status:', $purchase->status == 1 ? 'Completed' : 'Draft'],
                    ['Payment:', ucfirst($purchase->payment_status ?? 'Pending')],
                ];
                foreach ($rightInfo as $d) {
                    $sheet->setCellValue("D{$rightRow}", $d[0]);
                    $sheet->getStyle("D{$rightRow}")->getFont()->setBold(true);
                    $sheet->getStyle("D{$rightRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->mergeCells("E{$rightRow}:F{$rightRow}");
                    $sheet->setCellValue("E{$rightRow}", $d[1]);
                    $rightRow++;
                }

                $row = max($infoRow, $rightRow) + 1;

                // ── VENDOR DETAILS ──
                $sheet->setCellValue("A{$row}", 'VENDOR DETAILS');
                $sheet->getStyle("A{$row}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$row}:G{$row}");
                $row++;
                $vData = [
                    ['Name:', $vendor?->shop_name ?? 'N/A'],
                    ['Email:', $vendor?->email ?? 'N/A'],
                    ['Phone:', $vendor?->phone ?? 'N/A'],
                    ['Address:', $vendor?->address ?? 'N/A'],
                ];
                foreach ($vData as $d) {
                    $sheet->setCellValue("A{$row}", $d[0]);
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->mergeCells("B{$row}:G{$row}");
                    $sheet->setCellValue("B{$row}", $d[1]);
                    $row++;
                }
                $row++;

                // ── PRODUCT TABLE ──
                $sheet->setCellValue("A{$row}", 'PRODUCT DETAILS');
                $sheet->getStyle("A{$row}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$row}:G{$row}");
                $row++;
 
                $headers = ['Image', 'Product Name', 'Variants', 'Product No', 'Qty', 'Unit Cost', 'Total'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $row, $header);
                    $sheet->getStyle($col . $row)->applyFromArray($this->tableHeader);
                    $col++;
                }
                $headerRow = $row;
                $row++;

                $evenRow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]];
                $totalQty = 0;
                $isEven = false;

                foreach ($purchase->details as $detail) {
                    $totalQty += $detail->qty;
                    $col = 'A';
                    $hasImage = false;

                    $imagePath = $detail->product?->thumb_image;
                    if ($imagePath) {
                        $fp = public_path($imagePath);
                        if (!file_exists($fp)) {
                            $fp = storage_path('app/public/' . ltrim($imagePath, '/'));
                        }
                        if (file_exists($fp)) {
                            $hasImage = true;
                            $dwg = new Drawing();
                            $dwg->setPath($fp);
                            $dwg->setHeight(45);
                            $dwg->setCoordinates('A' . $row);
                            $dwg->setOffsetX(3);
                            $dwg->setOffsetY(3);
                            $dwg->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(50);
                        }
                    }
                    $sheet->getStyle('A' . $row)->applyFromArray($this->cellBorder);
                    if ($isEven) $sheet->getStyle('A' . $row)->applyFromArray($evenRow);
                    $col = 'B';

                    $rowStyle = $isEven ? $evenRow : [];
                    $variantStr = '';
                    if ($detail->variant_info) {
                        $parts = [];
                        foreach ($detail->variant_info as $vName => $vQty) {
                            $parts[] = $vName . ': ' . $vQty;
                        }
                        $variantStr = implode(', ', $parts);
                    }
                    $data = [
                        $detail->product?->name ?? 'N/A',
                        $variantStr ?: '—',
                        $detail->product?->product_number ?? 'N/A',
                        $detail->qty,
                        $currency . number_format($detail->unit_cost ?? 0, 2),
                        $currency . number_format($detail->total ?? 0, 2),
                    ];
                    $alignments = [
                        Alignment::HORIZONTAL_LEFT,
                        Alignment::HORIZONTAL_LEFT,
                        Alignment::HORIZONTAL_CENTER,
                        Alignment::HORIZONTAL_CENTER,
                        Alignment::HORIZONTAL_RIGHT,
                        Alignment::HORIZONTAL_RIGHT,
                    ];
                    foreach ($data as $idx => $val) {
                        $sheet->setCellValue($col . $row, $val);
                        $sheet->getStyle($col . $row)->applyFromArray($this->cellBorder);
                        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal($alignments[$idx]);
                        if ($isEven) $sheet->getStyle($col . $row)->applyFromArray($evenRow);
                        $col++;
                    }

                    if (!$hasImage) $sheet->getRowDimension($row)->setRowHeight(18);
                    $isEven = !$isEven;
                    $row++;
                }

                // ── SUMMARY ──
                $summaryRow = $row;
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue("E{$row}", 'GRAND TOTAL (LOCAL)');
                $sheet->setCellValue("F{$row}", $totalQty);
                $sheet->setCellValue("G{$row}", $currency . number_format($purchase->total_amount ?? 0, 2));
                $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($this->cellBorder);
                foreach (['E', 'F', 'G'] as $c) {
                    $sheet->getStyle($c . $row)->applyFromArray($this->summaryLabel);
                    $sheet->getStyle($c . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                }
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;

                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue("E{$row}", 'Paid');
                $sheet->getStyle("E{$row}")->applyFromArray($this->summaryLabel);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->setCellValue("F{$row}", '');
                $sheet->setCellValue("G{$row}", $currency . number_format($purchase->paid_amount ?? 0, 2));
                $sheet->getStyle("G{$row}")->applyFromArray($this->summaryValue);
                foreach (['A', 'B', 'C', 'D', 'F'] as $c) {
                    $sheet->getStyle($c . $row)->applyFromArray($this->cellBorder);
                }
                $row++;

                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue("E{$row}", 'Due');
                $sheet->getStyle("E{$row}")->applyFromArray($this->summaryLabel);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E{$row}")->getFont()->setColor(new Color('CC0000'));
                $sheet->setCellValue("F{$row}", '');
                $sheet->setCellValue("G{$row}", $currency . number_format($purchase->due_amount ?? 0, 2));
                $sheet->getStyle("G{$row}")->applyFromArray($this->summaryValue);
                $sheet->getStyle("G{$row}")->getFont()->setColor(new Color('CC0000'));
                foreach (['A', 'B', 'C', 'D', 'F'] as $c) {
                    $sheet->getStyle($c . $row)->applyFromArray($this->cellBorder);
                }
                $row++;

                // borders for summary
                for ($r = $summaryRow; $r < $row; $r++) {
                    for ($c = 'E'; $c <= 'G'; $c++) {
                        $sheet->getStyle($c . $r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }
                }
                // table borders
                for ($r = $headerRow; $r < $summaryRow; $r++) {
                    for ($c = 'A'; $c <= 'G'; $c++) {
                        $sheet->getStyle($c . $r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }
                }

                // info section borders
                for ($r = $row - 5; $r < $row; $r++) {
                    $sheet->getStyle("A{$r}:G{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
            },
        ];
    }
}
