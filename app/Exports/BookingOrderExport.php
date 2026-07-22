<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BookingOrderExport implements FromArray, WithCustomStartCell, ShouldAutoSize, WithEvents
{
    protected string $bookingNo;
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

    public function __construct(string $bookingNo)
    {
        $this->bookingNo = $bookingNo;
    }

    public function array(): array { return []; }
    public function startCell(): string { return 'Z100'; }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $items = Booking::where('booking_no', $this->bookingNo)
                    ->with(['product', 'vendor', 'unit'])
                    ->get();
                if ($items->isEmpty()) return;

                $first = $items->first();
                $vendor = $first->vendor;
                $sheet = $event->writer->getDelegate()->getActiveSheet();
                $row = 1;

                // ── HEADER BAR ──
                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->setCellValue("A{$row}", 'ORDER PLACE');
                $sheet->getStyle("A{$row}")->applyFromArray($this->darkHeader);
                $sheet->getStyle("A{$row}")->getFont()->setSize(14);
                $sheet->getRowDimension($row)->setRowHeight(36);
                $row += 2;

                // ── INFO SECTION (Left: Order / Right: Vendor) ──
                $infoRow = $row;
                $sheet->setCellValue("A{$infoRow}", 'ORDER INFORMATION');
                $sheet->getStyle("A{$infoRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$infoRow}:B{$infoRow}");
                $infoRow++;
                $leftInfo = [
                    ['Order No:', $this->bookingNo],
                    ['Status:', ucfirst($first->status)],
                    ['Shipping:', $first->shipping_method ?? 'N/A'],
                ];
                foreach ($leftInfo as $d) {
                    $sheet->setCellValue("A{$infoRow}", $d[0]);
                    $sheet->getStyle("A{$infoRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$infoRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->setCellValue("B{$infoRow}", $d[1]);
                    $infoRow++;
                }

                $rightRow = $row;
                $sheet->setCellValue("D{$rightRow}", 'VENDOR DETAILS');
                $sheet->getStyle("D{$rightRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("D{$rightRow}:E{$rightRow}");
                $rightRow++;
                $vData = [
                    ['Name:', $vendor?->shop_name ?? 'N/A'],
                    ['Email:', $vendor?->email ?? 'N/A'],
                    ['Phone:', $vendor?->phone ?? 'N/A'],
                    ['Address:', $vendor?->address ?? 'N/A'],
                ];
                foreach ($vData as $d) {
                    $sheet->setCellValue("D{$rightRow}", $d[0]);
                    $sheet->getStyle("D{$rightRow}")->getFont()->setBold(true);
                    $sheet->getStyle("D{$rightRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->setCellValue("E{$rightRow}", $d[1]);
                    $rightRow++;
                }

                $row = max($infoRow, $rightRow) + 1;

                // ── PRODUCT TABLE ──
                $sheet->setCellValue("A{$row}", 'ORDER DETAILS');
                $sheet->getStyle("A{$row}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$row}:F{$row}");
                $row++;
 
                $headers = ['Image', 'Product Name', 'Variants', 'Product Number', 'Quantity', 'Unit'];
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

                foreach ($items as $item) {
                    $totalQty += $item->qty;
                    $col = 'A';
                    $hasImage = false;

                    $imagePath = $item->product?->thumb_image;
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

                    $variantStr = '';
                    if ($item->variant_info) {
                        $parts = [];
                        foreach ($item->variant_info as $vName => $vQty) {
                            $parts[] = $vName . ': ' . $vQty;
                        }
                        $variantStr = implode(', ', $parts);
                    }
                    $data = [
                        $item->product?->name ?? 'N/A',
                        $variantStr ?: '—',
                        $item->product?->product_number ?? 'N/A',
                        $item->qty,
                        $item->unit?->name ?? 'N/A',
                    ];
                    $alignments = [
                        Alignment::HORIZONTAL_LEFT,
                        Alignment::HORIZONTAL_LEFT,
                        Alignment::HORIZONTAL_CENTER,
                        Alignment::HORIZONTAL_CENTER,
                        Alignment::HORIZONTAL_CENTER,
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

                // ── GRAND TOTAL ──
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("D{$row}", 'Grand Total');
                $sheet->getStyle("D{$row}")->getFont()->setBold(true);
                $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $sheet->setCellValue("E{$row}", $totalQty);
                $sheet->getStyle("E{$row}")->getFont()->setBold(true);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $sheet->setCellValue("F{$row}", '');
                $sheet->getStyle("F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $sheet->getStyle("A{$row}:C{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$row}:C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $row++;

                // borders
                for ($r = $headerRow; $r < $row; $r++) {
                    for ($c = 'A'; $c <= 'F'; $c++) {
                        $sheet->getStyle($c . $r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }
                }
            },
        ];
    }
}
