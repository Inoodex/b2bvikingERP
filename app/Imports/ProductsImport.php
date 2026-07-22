<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Vendor;
use App\Models\Color;
use App\Models\Size;
use App\Models\ProductType;
use App\Models\InventoryStock;
use App\Models\StockLedger;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProductsImport
{
    private $embeddedImages = [];
    
    public function import($filePath, $originalName = null)
    {
        // Get extension from original filename if provided
        if ($originalName) {
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        } else {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        }
        
        // Log::info('Original filename: ' . ($originalName ?? 'none'));
        // Log::info('Detected extension: ' . $extension);
        
        // For Excel files (.xlsx), extract embedded images first with coordinate mapping
        if (in_array($extension, ['xlsx', 'xls'])) {
            $this->embeddedImages = $this->extractImagesFromExcel($filePath);
            // Log::info('Extracted coordinate-mapped images count: ' . count($this->embeddedImages));
        }
        
        // For CSV files
        if ($extension === 'csv') {
            return $this->importCsv($filePath);
        }
        
        // For Excel files
        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->importExcel($filePath);
        }
        
        throw new \Exception('Unsupported file format: ' . $extension . '. Please use CSV format.');
    }

    public function getPreviewData($filePath, $originalName = null)
    {
        if ($originalName) {
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        } else {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        }

        // Log::info('Generating preview for file: ' . $filePath . ' (Extension: ' . $extension . ')');

        if ($extension === 'csv') {
            $data = $this->getPreviewCsv($filePath);
            // Log::info('CSV Preview generated. Headers count: ' . count($data['headers']) . ', Data rows: ' . count($data['data']));
            return $data;
        }

        if (in_array($extension, ['xlsx', 'xls'])) {
            $data = $this->getPreviewExcel($filePath);
            // Log::info('Excel Preview generated. Headers count: ' . count($data['headers']) . ', Data rows: ' . count($data['data']));
            return $data;
        }

        throw new \Exception('Unsupported file format: ' . $extension);
    }

    private function getPreviewCsv($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Could not open file');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Could not read header row');
        }

        $headerCount = count($headers);
        $data = [];
        $count = 0;
        
        // Find image column index in CSV
        $imageColIndex = -1;
        foreach ($headers as $idx => $h) {
            if (in_array(strtolower(trim($h)), ['image', 'image_url', 'img'])) {
                $imageColIndex = $idx;
                break;
            }
        }

        while (($row = fgetcsv($handle)) !== false && $count < 1000) {
            // Pad row to match header count
            $rowWithPadding = array_pad($row, $headerCount, '');
            
            // Check for local image path in image column
            if ($imageColIndex !== -1 && !empty($rowWithPadding[$imageColIndex])) {
                $imageVal = $rowWithPadding[$imageColIndex];
                if (!filter_var($imageVal, FILTER_VALIDATE_URL) && file_exists($imageVal)) {
                    $rowWithPadding[$imageColIndex] = $this->convertToBase64($imageVal);
                }
            }
            
            $data[] = $rowWithPadding;
            $count++;
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'data' => $data
        ];
    }

    private function getPreviewExcel($filePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Could not open Excel file');
        }

        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml')) {
            $strings = $zip->getFromName('xl/sharedStrings.xml');
            $xml = simplexml_load_string($strings);
            foreach ($xml->si as $item) {
                $sharedStrings[] = (string)$item->t;
            }
        }

        if (!$zip->locateName('xl/worksheets/sheet1.xml')) {
            $zip->close();
            throw new \Exception('Could not find sheet1');
        }

        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $xml = simplexml_load_string($sheet);
        $rows = $xml->sheetData->row;

        if (empty($rows)) {
            throw new \Exception('No data found in Excel file');
        }

        $headers = [];
        $maxHeaderIndex = 0;
        $firstRow = $rows[0]->c;
        foreach ($firstRow as $cell) {
            $coord = (string)$cell['r'];
            preg_match('/^[A-Z]+/', $coord, $matches);
            $colName = $matches[0];
            $colIndex = $this->columnLetterToIndex($colName);
            $headers[$colIndex] = $this->getCellValue($cell, $sharedStrings);
            if ($colIndex > $maxHeaderIndex) $maxHeaderIndex = $colIndex;
        }
        
        // Ensure headers array is continuous
        $finalHeaders = [];
        for ($i = 0; $i <= $maxHeaderIndex; $i++) {
            $finalHeaders[$i] = $headers[$i] ?? '';
        }
        $headers = $finalHeaders;
        $totalHeaderCount = count($headers);

        // Find image column index
        $imageColIndex = -1;
        foreach ($headers as $idx => $h) {
            $headerText = strtolower(trim($h));
            if (in_array($headerText, ['image', 'image_url', 'img', 'product_image', 'thumb_image'])) {
                $imageColIndex = $idx;
                // Log::info("Found image column at index: {$idx} (Header: {$h})");
                break;
            }
        }

        $data = [];
        for ($i = 1; $i < count($rows) && $i <= 1000; $i++) {
            $row = $rows[$i];
            $excelRowIndex = (int)$row['r'] - 1;
            $cells = $row->c;
            $rowData = array_pad([], $totalHeaderCount, '');
            
            foreach ($cells as $cell) {
                $coord = (string)$cell['r'];
                preg_match('/^[A-Z]+/', $coord, $matches);
                if (isset($matches[0])) {
                    $colName = $matches[0];
                    $colIndex = $this->columnLetterToIndex($colName);
                    if ($colIndex < $totalHeaderCount) {
                        $rowData[$colIndex] = $this->getCellValue($cell, $sharedStrings);
                    }
                }
            }
            
            // Try matching with extracted images if available
            if (empty($this->embeddedImages)) {
                $this->embeddedImages = $this->extractImagesFromExcel($filePath);
            }
            
            // For preview, we try to match embedded images by coordinate
            if ($imageColIndex !== -1 && (empty($rowData[$imageColIndex]) || !filter_var($rowData[$imageColIndex], FILTER_VALIDATE_URL))) {
                $colLetter = $this->indexToColumnLetter($imageColIndex);
                $coordinate = $colLetter . ($i + 1); // +1 because i is 1-indexed for data rows, excel row index is i+1
                
                if (isset($this->embeddedImages[$coordinate])) {
                    $imgRelPath = $this->embeddedImages[$coordinate];
                    $imgPath = Storage::disk('public')->path($imgRelPath);
                    if (file_exists($imgPath)) {
                        $rowData[$imageColIndex] = $this->convertToBase64($imgPath);
                        // Log::info("Row {$i}: Matched embedded image {$imgRelPath} at {$coordinate}");
                    } else {
                        Log::warning("Row {$i}: Embedded image file not found at {$imgPath} for {$coordinate}");
                    }
                } else {
                    // Log::info("Row {$i}: No embedded image found at coordinate {$coordinate}");
                }
            }
            
            // Convert existing local paths to Base64 for preview
            if ($imageColIndex !== -1 && !empty($rowData[$imageColIndex])) {
                $imageVal = $rowData[$imageColIndex];
                if (!filter_var($imageVal, FILTER_VALIDATE_URL) && !str_starts_with($imageVal, 'data:')) {
                    $fullPath = @Storage::disk('public')->path($imageVal);
                    if ($fullPath && file_exists($fullPath)) {
                        $rowData[$imageColIndex] = $this->convertToBase64($fullPath);
                    }
                }
            }
            
            $data[] = $rowData;
        }

        return [
            'headers' => $headers,
            'data' => $data
        ];
    }
    
    
    // Import from Excel file
    private function importExcel($filePath)
    {
        // Simple XML parsing for Excel
        $results = [
            'success' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
            'created_product_ids' => [],
        ];
        
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            throw new \Exception('Could not open Excel file');
        }
        
        // Read shared strings
        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml')) {
            $strings = $zip->getFromName('xl/sharedStrings.xml');
            $xml = simplexml_load_string($strings);
            foreach ($xml->si as $item) {
                $sharedStrings[] = (string)$item->t;
            }
        }
        
        // Read sheet1
        if (!$zip->locateName('xl/worksheets/sheet1.xml')) {
            $zip->close();
            throw new \Exception('Could not find sheet1');
        }
        
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        
        $xml = simplexml_load_string($sheet);
        $rows = $xml->sheetData->row;
        
        if (empty($rows)) {
            throw new \Exception('No data found in Excel file');
        }
        
        // Get headers from first row
        $headers = [];
        $firstRow = $rows[0]->c;
        foreach ($firstRow as $cell) {
            $col = (string)$cell['r'];
            $colLetter = preg_replace('/[0-9]/', '', $col);
            $value = $this->getCellValue($cell, $sharedStrings);
            $headers[$colLetter] = strtolower(trim($value));
        }
        
        // Log::info('Excel headers: ' . json_encode($headers));
        
        // Process data rows
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $cells = $row->c;
            $rowIndex = (int)$row['r'];
            
            // First map cells to columns for easy lookup
            $cellMap = [];
            foreach ($cells as $cell) {
                $col = (string)$cell['r'];
                $colLetter = preg_replace('/[0-9]/', '', $col);
                $cellMap[$colLetter] = $cell;
            }

            $rowData = [];
            foreach ($headers as $colLetter => $headerKey) {
                $cell = $cellMap[$colLetter] ?? null;
                $value = $cell ? $this->getCellValue($cell, $sharedStrings) : '';
                $rowData[$headerKey] = $value;
                
                // Store actual coordinate if this is an image column
                if (in_array($headerKey, ['image', 'image_url', 'img', 'product_image', 'thumb_image'])) {
                    $rowData['_image_coordinate'] = $colLetter . $rowIndex;
                }
            }
            
            // Log::info("Importing Row {$rowIndex} with data: " . json_encode($rowData));
            try {
                $status = $this->processExcelRow($rowData, $rowIndex);
                if ($status === 'skipped') {
                    $results['skipped']++;
                } else {
                    $results['success']++;
                    if ($status instanceof Product) {
                        $results['created_product_ids'][] = (int) $status->id;
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = 'Row ' . $rowIndex . ': ' . $e->getMessage();
            }
        }

        $results['created_product_ids'] = array_values(array_unique(array_filter(
            array_map(static fn ($id): int => (int) $id, $results['created_product_ids']),
            static fn ($id): bool => $id > 0
        )));
        
        return $results;
    }
    
    private function getCellValue($cell, $sharedStrings)
    {
        $type = (string)$cell['t'] ?? '';
        $value = (string)$cell->v ?? '';
        
        if ($type === 's') {
            // Shared string
            $index = intval($value);
            return $sharedStrings[$index] ?? '';
        } elseif ($type === 'str') {
            // Formula string
            return (string)$cell->f ?? '';
        }
        
        return $value;
    }
    
    private function processExcelRow($rowData, $excelRowIndex = null)
    {
        DB::beginTransaction();
        
        try {
            // Map Excel columns
            $getValue = function($key, $default = null) use ($rowData) {
                // Try multiple column variations based on the key
                $keyVariations = [];
                
                if ($key === 'color_name') {
                    $keyVariations = ['color_name', 'color', 'colour', 'variant_1_color_name', 'variant_1_color'];
                } elseif ($key === 'size_name') {
                    $keyVariations = ['size_name', 'size', 'variant_1_size_name', 'variant_1_size'];
                } elseif ($key === 'variant_price') {
                    $keyVariations = ['variant_price', 'var_price', 'vprice', 'selling_price', 'variant_1_price', 'variant_1_selling_price', 'variant_1_size_price'];
                } elseif ($key === 'variant_outlet_price') {
                    $keyVariations = ['variant_outlet_price', 'variant_outlet', 'wholesale_price', 'whole_sale_price', 'variant_1_outlet_price', 'variant_1_wholesale_price'];
                } elseif ($key === 'variant_qty') {
                    $keyVariations = ['variant_qty', 'variant_quantity', 'var_qty', 'vqty', 'variant_1_qty', 'variant_1_size_qty'];
                } elseif ($key === 'image') {
                    $keyVariations = ['image', 'image_url', 'img', 'product_image', 'thumb_image'];
                } elseif ($key === 'product_type') {
                    $keyVariations = ['product_type', 'producttype', 'type', 'product_type_name'];
                } elseif ($key === 'minimum_order_qty') {
                    $keyVariations = ['minimum_order_qty', 'min_order_qty', 'minimum_qty', 'minimum_order_quantity'];
                } elseif ($key === 'custom_label') {
                    $keyVariations = ['custom_label', 'customlabel', 'label', 'customer_label'];
                } else {
                    $keyVariations = [$key, str_replace('_', '', $key), str_replace(' ', '_', $key)];
                }
                
                foreach ($keyVariations as $k) {
                    if (isset($rowData[$k]) && !empty(trim($rowData[$k]))) {
                        return trim($rowData[$k]);
                    }
                    // Also try lowercase
                    if (isset($rowData[strtolower($k)]) && !empty(trim($rowData[strtolower($k)]))) {
                        return trim($rowData[strtolower($k)]);
                    }
                }
                return $default;
            };

            $name = $getValue('name', 'Untitled Product');
            $productNumber = $getValue('product_number');
            
            // Get image - use coordinate if available
            $imagePath = null;
            $imageValue = $getValue('image');
            $coordinate = $rowData['_image_coordinate'] ?? null;
            
            // Log::info("Image Processing Check - Row {$excelRowIndex}: imageValue='" . ($imageValue ?? 'null') . "', coordinate='" . ($coordinate ?? 'null') . "'");
            // Log::info("Embedded images keys: " . json_encode(array_keys($this->embeddedImages)));
            
            if (!empty($imageValue) || !empty($coordinate)) {
                $imagePath = $this->handleImage($imageValue, $coordinate);
                // Log::info("Image Processing Match Found - Result: " . ($imagePath ?: 'FAILED'));
            } else {
                // Log::info("Image Processing - No value or coordinate for row {$excelRowIndex}");
            }
            
            // ========== Duplicate Check (OR Logic) ==========
            // Log::info('Excel - Product name: ' . $name . ', product_number: ' . ($productNumber ?? 'empty'));

            $existingQuery = Product::query();
            $existingQuery->where(function($q) use ($name, $productNumber) {
                if (!empty($name)) {
                    $q->where('name', $name);
                }
                if (!empty($productNumber)) {
                    $q->orWhere('product_number', $productNumber);
                }
            });

            if ($existingQuery->exists()) {
                DB::rollBack();
                // Log::info('Excel - Skipped: Product with same name or number already exists.');
                return 'skipped';
            }
            
            $product = new Product();
            $product->thumb_image = $imagePath;
            $product->name = $name;
            $product->slug = Str::slug($name);
            $product->product_number = $productNumber;
            
            // Create new product attributes
            $product->category_id = null;
            $product->sub_category_id = null;
            $product->child_category_id = null;
            $product->brand_id = null;
            $product->vendor_id = null;
            $product->unit_id = null;
            $product->product_number = $productNumber;
            $product->long_description = $getValue('description') ?? $getValue('long_description');
            $product->purchase_price = floatval($getValue('purchase_price', 0));
            $product->price = floatval($getValue('price', 0));
            $product->outlet_price = floatval($getValue('outlet_price', 0));
            $product->barcode = $getValue('barcode');
            $product->self_number = $getValue('self_number');
            $product->custom_label = $getValue('custom_label');
            $product->status = intval($getValue('status', 1));
            $product->raw_material_cost = floatval($getValue('raw_material_cost', 0));
            $product->transport_cost = floatval($getValue('transport_cost', 0));
            $product->tax = floatval($getValue('tax', 0));
            $discountType = strtolower(trim((string) $getValue('discount_type', '')));
            $discountValue = max(0, floatval($getValue('discount', 0)));
            if (!in_array($discountType, ['flat', 'percent'], true) || $discountValue <= 0) {
                $discountType = null;
                $discountValue = 0;
            } elseif ($discountType === 'percent' && $discountValue > 100) {
                $discountValue = 100;
            }

            $vatType = strtolower(trim((string) $getValue('vat_type', '')));
            $vatValue = max(0, floatval($getValue('vat_value', 0)));
            if (!in_array($vatType, ['flat', 'percent'], true) || $vatValue <= 0) {
                $vatType = null;
                $vatValue = null;
            } elseif ($vatType === 'percent' && $vatValue > 100) {
                $vatValue = 100;
            }

            $product->discount_type = $discountType;
            $product->discount = $discountValue;
            $product->vat_type = $vatType;
            $product->vat_value = $vatValue;
            $product->qty = intval($getValue('qty', 0));
            
            // ========== Look up Category, Brand, Vendor, Unit ==========
            // Category
            $catId = $getValue('category_id');
            if (!empty($catId)) {
                $category = Category::find($catId);
                $product->category_id = $category ? $category->id : null;
            } else {
                $catName = $getValue('category_name');
                // Log::info('Excel - Looking up category: ' . ($catName ?? 'not provided'));
                if (!empty($catName)) {
                    $category = Category::where('name', 'like', '%' . $catName . '%')->first();
                    $product->category_id = $category ? $category->id : null;
                    // Log::info('Excel - Category result: ' . ($category ? $category->name : 'NOT FOUND'));
                }
            }
            
            // Sub Category
            if ($product->category_id) {
                $subCatId = $getValue('sub_category_id');
                if (!empty($subCatId)) {
                    $subCategory = SubCategory::find($subCatId);
                    $product->sub_category_id = $subCategory ? $subCategory->id : null;
                } else {
                    $subCatName = $getValue('sub_category_name');
                    if (!empty($subCatName)) {
                        $subCategory = SubCategory::where('name', 'like', '%' . $subCatName . '%')
                            ->where('category_id', $product->category_id)
                            ->first();
                        $product->sub_category_id = $subCategory ? $subCategory->id : null;
                    }
                }
                
                // Child Category
                if ($product->sub_category_id) {
                    $childCatId = $getValue('child_category_id');
                    if (!empty($childCatId)) {
                        $childCategory = ChildCategory::find($childCatId);
                        $product->child_category_id = $childCategory ? $childCategory->id : null;
                    } else {
                        $childCatName = $getValue('child_category_name');
                        if (!empty($childCatName)) {
                            $childCategory = ChildCategory::where('name', 'like', '%' . $childCatName . '%')
                                ->where('sub_category_id', $product->sub_category_id)
                                ->first();
                            $product->child_category_id = $childCategory ? $childCategory->id : null;
                        }
                    }
                }
            }
            
            // Brand
            $brandIdVal = $getValue('brand_id');
            if (!empty($brandIdVal)) {
                $brand = Brand::find($brandIdVal);
                $product->brand_id = $brand ? $brand->id : null;
            } else {
                $brandName = $getValue('brand_name');
                // Log::info('Excel - Looking up brand: ' . ($brandName ?? 'not provided'));
                if (!empty($brandName)) {
                    $brand = Brand::where('name', 'like', '%' . $brandName . '%')->first();
                    $product->brand_id = $brand ? $brand->id : null;
                    // Log::info('Excel - Brand result: ' . ($brand ? $brand->name : 'NOT FOUND'));
                }
            }
            
            // Vendor
            $vendorIdVal = $getValue('vendor_id');
            if (!empty($vendorIdVal)) {
                $vendor = Vendor::find($vendorIdVal);
                $product->vendor_id = $vendor ? $vendor->id : null;
            } else {
                $vendorName = $getValue('vendor_name');
                // Log::info('Excel - Looking up vendor: ' . ($vendorName ?? 'not provided'));
                if (!empty($vendorName)) {
                    $vendor = Vendor::where('shop_name', 'like', '%' . $vendorName . '%')->first();
                    $product->vendor_id = $vendor ? $vendor->id : null;
                    // Log::info('Excel - Vendor result: ' . ($vendor ? $vendor->shop_name : 'NOT FOUND'));
                }
            }
            
            // Unit
            $unitIdVal = $getValue('unit_id');
            if (!empty($unitIdVal)) {
                $unit = Unit::find($unitIdVal);
                $product->unit_id = $unit ? $unit->id : null;
            } else {
                $unitName = $getValue('unit_name');
                if (!empty($unitName)) {
                    $unit = Unit::where('name', 'like', '%' . $unitName . '%')->first();
                    $product->unit_id = $unit ? $unit->id : null;
                }
            }
            // Product Type
            $product->product_type = $getValue('product_type');
            $productTypeId = $getValue('product_type_id');
            if (!empty($productTypeId) && ProductType::find((int) $productTypeId)) {
                $product->product_type_id = (int) $productTypeId;
            } else {
                $product->product_type_id = null;
            }
            $product->minimum_order_qty = max(1, intval($getValue('minimum_order_qty', 1)));
            
            $product->save();
            
            // ========== Handle Product Opening Stock ==========
            if ($product->qty > 0) {
                $stock = InventoryStock::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1
                ]);
                $stock->increment('quantity', $product->qty);

                StockLedger::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1,
                    'reference_type' => 'opening',
                    'reference_id' => $product->id,
                    'in_qty' => $product->qty,
                    'out_qty' => 0,
                    'balance_qty' => $stock->quantity,
                    'date' => date('Y-m-d')
                ]);
            }

            // ========== Handle Combined Variants (color + size pair) ==========
            $variantsAddedCount = 0;
            $variantIndexes = $this->collectVariantIndexes(array_keys($rowData));
            $lastColorName = null;
            $lastSizeName = null;

            foreach ($variantIndexes as $i) {
                $varColorName = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_color_name',
                    'variant_{i}_color',
                    'color_name_{i}',
                    'color_{i}',
                ]);
                $varSizeName = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_size_name',
                    'variant_{i}_size',
                    'size_name_{i}',
                    'size_{i}',
                ]);

                if (($varColorName === null || $varColorName === '') && $varSizeName !== null && $lastColorName !== null) {
                    $varColorName = $lastColorName;
                }
                if (($varSizeName === null || $varSizeName === '') && $varColorName !== null && $lastSizeName !== null) {
                    $varSizeName = $lastSizeName;
                }
                if ($varColorName !== null && $varColorName !== '') {
                    $lastColorName = $varColorName;
                }
                if ($varSizeName !== null && $varSizeName !== '') {
                    $lastSizeName = $varSizeName;
                }

                $varQtyRaw = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_qty',
                    'variant_{i}_quantity',
                    'variant_{i}_stock',
                    'qty_{i}',
                    'quantity_{i}',
                    'stock_{i}',
                    'variant_{i}_size_qty',
                ]);
                $varOutletPriceRaw = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_outlet_price',
                    'variant_{i}_outlet',
                    'variant_{i}_wholesale_price',
                    'variant_{i}_whole_sale_price',
                    'outlet_price_{i}',
                    'outlet_{i}',
                    'wholesale_price_{i}',
                    'whole_sale_price_{i}',
                ]);
                $varPriceRaw = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_price',
                    'variant_{i}_selling_price',
                    'price_{i}',
                    'selling_price_{i}',
                    'variant_{i}_size_price',
                ]);

                $created = $this->createCombinedVariant(
                    $product,
                    $varColorName,
                    $varSizeName,
                    $varPriceRaw !== null ? (float) $varPriceRaw : null,
                    $varOutletPriceRaw !== null ? (float) $varOutletPriceRaw : null,
                    $varQtyRaw !== null ? (int) $varQtyRaw : 0
                );

                if ($created) {
                    $variantsAddedCount++;
                }
            }
            
            // Only use old format if NO variants were added in the loop above
            if ($variantsAddedCount === 0) {
                $colorName = $getValue('color_name');
                $sizeName = $getValue('size_name');
                $variantPrice = $getValue('variant_price');
                $variantOutletPrice = $getValue('variant_outlet_price');
                $variantQty = $getValue('variant_qty', 0);

                $this->createCombinedVariant(
                    $product,
                    $colorName,
                    $sizeName,
                    $variantPrice !== null && $variantPrice !== '' ? (float) $variantPrice : null,
                    $variantOutletPrice !== null && $variantOutletPrice !== '' ? (float) $variantOutletPrice : null,
                    (int) $variantQty
                );
            }
            DB::commit();
            return $product;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function importCsv($filePath)
    {
        $results = [
            'success' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
            'created_product_ids' => [],
        ];
        
        if (!file_exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Could not open file');
        }
        
        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            throw new \Exception('Could not read header row');
        }
        
        // Clean headers
        $headers = array_map('trim', $headers);
        $headers = array_map('strtolower', $headers);
        
        // Map columns
        $columnMap = $this->mapColumns($headers);
        
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            try {
                $status = $this->processRow($row, $columnMap);
                if ($status === 'skipped') {
                    $results['skipped']++;
                } else {
                    $results['success']++;
                    if ($status instanceof Product) {
                        $results['created_product_ids'][] = (int) $status->id;
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                Log::error("Import Error Row {$rowNumber}: " . $e->getMessage());
            }
            
            // Clear memory periodically
            if ($rowNumber % 100 === 0) {
                gc_collect_cycles();
            }
        }
        
        fclose($handle);

        $results['created_product_ids'] = array_values(array_unique(array_filter(
            array_map(static fn ($id): int => (int) $id, $results['created_product_ids']),
            static fn ($id): bool => $id > 0
        )));
        
        return $results;
    }
    
    private function mapColumns($headers)
    {
        $map = [];
        
        // Log::info('Received headers from file: ' . implode(', ', array_map('trim', $headers)));
        
        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));
            
            // Name
            if (in_array($header, ['name', 'product_name', 'product'])) {
                $map['name'] = $index;
            }
            // Category
            elseif (in_array($header, ['category_id', 'categoryid'])) {
                $map['category_id'] = $index;
            }
            elseif (in_array($header, ['category_name', 'category', 'cat_name'])) {
                $map['category_name'] = $index;
            }
            // Sub Category
            elseif (in_array($header, ['sub_category_id', 'subcategoryid'])) {
                $map['sub_category_id'] = $index;
            }
            elseif (in_array($header, ['sub_category_name', 'sub_category', 'subcat_name'])) {
                $map['sub_category_name'] = $index;
            }
            // Brand
            elseif (in_array($header, ['brand_id', 'brandid'])) {
                $map['brand_id'] = $index;
            }
            elseif (in_array($header, ['brand_name', 'brand'])) {
                $map['brand_name'] = $index;
            }
            // Vendor
            elseif (in_array($header, ['vendor_id', 'vendorid'])) {
                $map['vendor_id'] = $index;
            }
            elseif (in_array($header, ['vendor_name', 'vendor'])) {
                $map['vendor_name'] = $index;
            }
            // Unit
            elseif (in_array($header, ['unit_id', 'unitid'])) {
                $map['unit_id'] = $index;
            }
            elseif (in_array($header, ['unit_name', 'unit'])) {
                $map['unit_name'] = $index;
            }
            // Product Type
            elseif (in_array($header, ['product_type_name', 'product_type', 'producttype', 'type'])) {
                $map['product_type'] = $index;
            }
            elseif (in_array($header, ['product_type_id', 'producttype_id'])) {
                $map['product_type_id'] = $index;
            }
            // Custom Label
            elseif (in_array($header, ['custom_label', 'customlabel', 'label', 'customer_label'])) {
                $map['custom_label'] = $index;
            }
            // Minimum Order Qty
            elseif (in_array($header, ['minimum_order_qty', 'min_order_qty', 'minimum_qty', 'minimum_order_quantity'])) {
                $map['minimum_order_qty'] = $index;
            }
            // Self Number
            elseif (in_array($header, ['self_number', 'selfnumber', 'self_no'])) {
                $map['self_number'] = $index;
            }
            // Child Category
            elseif (in_array($header, ['child_category_id', 'childcategoryid'])) {
                $map['child_category_id'] = $index;
            }
            elseif (in_array($header, ['child_category_name', 'child_category', 'childcat_name'])) {
                $map['child_category_name'] = $index;
            }
            // Other fields
            elseif ($header === 'product_number' || $header === 'product_no') {
                $map['product_number'] = $index;
            }
            elseif ($header === 'barcode') {
                $map['barcode'] = $index;
            }
            elseif (in_array($header, ['description', 'long_description', 'long_desc'])) {
                $map['long_description'] = $index;
            }
            elseif (in_array($header, ['purchase_price', 'cost_price', 'cost'])) {
                $map['purchase_price'] = $index;
            }
            elseif (in_array($header, ['price', 'sale_price', 'selling_price'])) {
                $map['price'] = $index;
            }
            elseif (in_array($header, ['outlet_price', 'outlet'])) {
                $map['outlet_price'] = $index;
            }
            elseif (in_array($header, ['qty', 'quantity', 'stock'])) {
                $map['qty'] = $index;
            }
            elseif (in_array($header, ['image', 'image_url', 'img', 'thumb_image', 'product_image'])) {
                $map['image'] = $index;
            }
            elseif (in_array($header, ['raw_material_cost', 'material_cost'])) {
                $map['raw_material_cost'] = $index;
            }
            elseif (in_array($header, ['transport_cost', 'transport'])) {
                $map['transport_cost'] = $index;
            }
            elseif ($header === 'tax') {
                $map['tax'] = $index;
            }
            elseif (in_array($header, ['discount_type', 'discounttype'])) {
                $map['discount_type'] = $index;
            }
            elseif (in_array($header, ['discount', 'discount_value'])) {
                $map['discount'] = $index;
            }
            elseif (in_array($header, ['vat_type', 'tax_type'])) {
                $map['vat_type'] = $index;
            }
            elseif (in_array($header, ['vat_value', 'tax_value'])) {
                $map['vat_value'] = $index;
            }
            elseif ($header === 'status') {
                $map['status'] = $index;
            }
            // Variant fields (single/fallback)
            elseif (in_array($header, ['color_name', 'color', 'colour'])) {
                $map['color_name'] = $index;
            }
            elseif (in_array($header, ['size_name', 'size'])) {
                $map['size_name'] = $index;
            }
            elseif (in_array($header, ['variant_price', 'selling_price'])) {
                $map['variant_price'] = $index;
            }
            elseif (in_array($header, ['variant_outlet_price', 'variant_outlet', 'wholesale_price', 'whole_sale_price'])) {
                $map['variant_outlet_price'] = $index;
            }
            elseif (in_array($header, ['variant_qty', 'variant_quantity'])) {
                $map['variant_qty'] = $index;
            }
            // Combined variant format:
            // variant_1_color_name, variant_1_size_name, variant_1_qty, variant_1_outlet_price, variant_1_price
            elseif (preg_match('/^variant_(\d+)_(color_name|color|colour)$/', $header, $matches)) {
                $map['variant_' . $matches[1] . '_color_name'] = $index;
            }
            elseif (preg_match('/^variant_(\d+)_(size_name|size)$/', $header, $matches)) {
                $map['variant_' . $matches[1] . '_size_name'] = $index;
            }
            elseif (preg_match('/^variant_(\d+)_(qty|quantity|stock|size_qty)$/', $header, $matches)) {
                $map['variant_' . $matches[1] . '_qty'] = $index;
            }
            elseif (preg_match('/^variant_(\d+)_(outlet_price|outlet|wholesale_price|whole_sale_price)$/', $header, $matches)) {
                $map['variant_' . $matches[1] . '_outlet_price'] = $index;
            }
            elseif (preg_match('/^variant_(\d+)_(price|selling_price|size_price)$/', $header, $matches)) {
                $map['variant_' . $matches[1] . '_price'] = $index;
            }
            // Alternate indexed format:
            // color_name_1, size_name_1, qty_1, outlet_price_1, price_1
            elseif (preg_match('/^(color_name|color|colour)_(\d+)$/', $header, $matches)) {
                $map['variant_' . $matches[2] . '_color_name'] = $index;
            }
            elseif (preg_match('/^(size_name|size)_(\d+)$/', $header, $matches)) {
                $map['variant_' . $matches[2] . '_size_name'] = $index;
            }
            elseif (preg_match('/^(qty|quantity|stock)_(\d+)$/', $header, $matches)) {
                $map['variant_' . $matches[2] . '_qty'] = $index;
            }
            elseif (preg_match('/^(outlet_price|outlet|wholesale_price|whole_sale_price)_(\d+)$/', $header, $matches)) {
                $map['variant_' . $matches[2] . '_outlet_price'] = $index;
            }
            elseif (preg_match('/^(price|selling_price|size_price)_(\d+)$/', $header, $matches)) {
                $map['variant_' . $matches[2] . '_price'] = $index;
            }
        }
        
        // Log::info('Mapped columns: ' . json_encode($map));
        
        return $map;
    }
    
    private function processRow($row, $columnMap)
    {
        DB::beginTransaction();
        
        try {
            // Get values from row
            $getValue = function($key, $default = null) use ($row, $columnMap) {
                // Try to find the key in columnMap
                if (!isset($columnMap[$key]) || !isset($row[$columnMap[$key]])) {
                    // Try variations for color and size
                    if ($key === 'color_name') {
                        $altKeys = ['color', 'colour', 'variant_1_color_name', 'variant_1_color'];
                    } elseif ($key === 'size_name') {
                        $altKeys = ['size', 'variant_1_size_name', 'variant_1_size'];
                    } elseif ($key === 'variant_price') {
                        $altKeys = ['variant_price', 'selling_price'];
                    } elseif ($key === 'variant_outlet_price') {
                        $altKeys = ['variant_outlet_price', 'variant_outlet', 'wholesale_price', 'whole_sale_price'];
                    } elseif ($key === 'variant_qty') {
                        $altKeys = ['variant_qty', 'variant_quantity'];
                    } elseif ($key === 'minimum_order_qty') {
                        $altKeys = ['minimum_order_qty', 'min_order_qty', 'minimum_qty', 'minimum_order_quantity'];
                    } elseif ($key === 'image') {
                        $altKeys = ['image', 'image_url', 'img', 'thumb_image', 'product_image'];
                    } elseif ($key === 'product_type') {
                        $altKeys = ['product_type', 'producttype', 'type', 'product_type_name'];
                    } elseif ($key === 'custom_label') {
                        $altKeys = ['custom_label', 'customlabel', 'label', 'customer_label'];
                    } else {
                        $altKeys = [];
                    }
                    
                    foreach ($altKeys as $altKey) {
                        if (isset($columnMap[$altKey]) && isset($row[$columnMap[$altKey]])) {
                            return trim($row[$columnMap[$altKey]]);
                        }
                    }
                    
                    return $default;
                }
                return trim($row[$columnMap[$key]]);
            };
            
            // ========== Image Handle ==========
            $imagePath = null;
            $imageUrl = $getValue('image');
            // Log::info("Image Processing - CSV Row: imageValue='{$imageUrl}'");
            if (!empty($imageUrl)) {
                $imagePath = $this->handleImage($imageUrl);
                // Log::info("Image Processing - Result: " . ($imagePath ?: 'NULL'));
            }

            // ========== Category ID ==========
            $categoryId = null;
            $catId = $getValue('category_id');
            if (!empty($catId)) {
                $category = Category::find($catId);
                $categoryId = $category ? $category->id : null;
            } else {
                $catName = $getValue('category_name');
                // Log::info('Looking for category: ' . ($catName ?? 'not provided'));
                if (!empty($catName)) {
                    $category = Category::where('name', 'like', '%' . $catName . '%')->first();
                    $categoryId = $category ? $category->id : null;
                    // Log::info('Category found: ' . ($category ? $category->name : 'not found'));
                }
            }

            // ========== Sub Category ID ==========
            $subCategoryId = null;
            $subCatId = $getValue('sub_category_id');
            if (!empty($subCatId)) {
                $subCategoryId = $subCatId;
            } else {
                $subCatName = $getValue('sub_category_name');
                if (!empty($subCatName) && $categoryId) {
                    $subCategory = SubCategory::where('category_id', $categoryId)
                        ->where('name', 'like', '%' . $subCatName . '%')
                        ->first();
                    $subCategoryId = $subCategory ? $subCategory->id : null;
                }
            }

            // ========== Child Category ID ==========
            $childCategoryId = null;
            $childCatId = $getValue('child_category_id');
            if (!empty($childCatId)) {
                $childCategoryId = $childCatId;
            } else {
                $childCatName = $getValue('child_category_name');
                if (!empty($childCatName) && $subCategoryId) {
                    $childCategory = ChildCategory::where('sub_category_id', $subCategoryId)
                        ->where('name', 'like', '%' . $childCatName . '%')
                        ->first();
                    $childCategoryId = $childCategory ? $childCategory->id : null;
                }
            }

            // ========== Brand ID ==========
            $brandId = null;
            $brandIdVal = $getValue('brand_id');
            if (!empty($brandIdVal)) {
                $brand = Brand::find($brandIdVal);
                $brandId = $brand ? $brand->id : null;
            } else {
                $brandName = $getValue('brand_name');
                // Log::info('Looking for brand: ' . ($brandName ?? 'not provided'));
                if (!empty($brandName)) {
                    $brand = Brand::where('name', 'like', '%' . $brandName . '%')->first();
                    $brandId = $brand ? $brand->id : null;
                    // Log::info('Brand found: ' . ($brand ? $brand->name : 'not found'));
                }
            }

            // ========== Vendor ID ==========
            $vendorId = null;
            $vendorIdVal = $getValue('vendor_id');
            if (!empty($vendorIdVal)) {
                $vendor = Vendor::find($vendorIdVal);
                $vendorId = $vendor ? $vendor->id : null;
            } else {
                $vendorName = $getValue('vendor_name');
                if (!empty($vendorName)) {
                    $vendor = Vendor::where('shop_name', 'like', '%' . $vendorName . '%')->first();
                    $vendorId = $vendor ? $vendor->id : null;
                }
            }

            // ========== Unit ID ==========
            $unitId = null;
            $unitIdVal = $getValue('unit_id');
            if (!empty($unitIdVal)) {
                $unit = Unit::find($unitIdVal);
                $unitId = $unit ? $unit->id : null;
            } else {
                $unitName = $getValue('unit_name');
                if (!empty($unitName)) {
                    $unit = Unit::where('name', 'like', '%' . $unitName . '%')->first();
                    $unitId = $unit ? $unit->id : null;
                }
            }
            
            // ========== Product Type ==========
            
            $productTypeNameRaw = $getValue('product_type');

            // ========== Duplicate Check (OR Logic) ==========
            $name = $getValue('name', 'Untitled Product');
            $productNumber = $getValue('product_number');
            
            $existingQuery = Product::query();
            $existingQuery->where(function($q) use ($name, $productNumber) {
                if (!empty($name)) {
                    $q->where('name', $name);
                }
                if (!empty($productNumber)) {
                    $q->orWhere('product_number', $productNumber);
                }
            });

            if ($existingQuery->exists()) {
                DB::rollBack();
                // Log::info('CSV - Skipped: Product with same name or number already exists.');
                return 'skipped';
            }

            // ========== Create Product ==========
            $product = new Product();
            $product->thumb_image = $imagePath;
            $product->name = $name;
            $product->slug = Str::slug($name);
            $product->category_id = $categoryId;
            $product->sub_category_id = $subCategoryId;
            $product->child_category_id = $childCategoryId;
            $product->brand_id = $brandId;
            $product->vendor_id = $vendorId;
            $product->unit_id = $unitId;
            $product->product_type = $productTypeNameRaw;
            $productTypeId = $getValue('product_type_id');
            if (!empty($productTypeId) && ProductType::find((int) $productTypeId)) {
                $product->product_type_id = (int) $productTypeId;
            } else {
                $product->product_type_id = null;
            }
            $product->product_number = $productNumber;
            $product->long_description = $getValue('long_description');
            $product->purchase_price = floatval($getValue('purchase_price', 0));
            $product->price = floatval($getValue('price', 0));
            $product->outlet_price = floatval($getValue('outlet_price', 0));
            $product->barcode = $getValue('barcode');
            $product->self_number = $getValue('self_number');
            $product->custom_label = $getValue('custom_label');
            $product->status = intval($getValue('status', 1));
            $product->raw_material_cost = floatval($getValue('raw_material_cost', 0));
            $product->transport_cost = floatval($getValue('transport_cost', 0));
            $product->tax = floatval($getValue('tax', 0));
            $discountType = strtolower(trim((string) $getValue('discount_type', '')));
            $discountValue = max(0, floatval($getValue('discount', 0)));
            if (!in_array($discountType, ['flat', 'percent'], true) || $discountValue <= 0) {
                $discountType = null;
                $discountValue = 0;
            } elseif ($discountType === 'percent' && $discountValue > 100) {
                $discountValue = 100;
            }

            $vatType = strtolower(trim((string) $getValue('vat_type', '')));
            $vatValue = max(0, floatval($getValue('vat_value', 0)));
            if (!in_array($vatType, ['flat', 'percent'], true) || $vatValue <= 0) {
                $vatType = null;
                $vatValue = null;
            } elseif ($vatType === 'percent' && $vatValue > 100) {
                $vatValue = 100;
            }

            $product->discount_type = $discountType;
            $product->discount = $discountValue;
            $product->vat_type = $vatType;
            $product->vat_value = $vatValue;
            $product->minimum_order_qty = max(1, intval($getValue('minimum_order_qty', 1)));
            $product->qty = intval($getValue('qty', 0));
            $product->save();

            // ========== Handle Product Opening Stock ==========
            if ($product->qty > 0) {
                $stock = InventoryStock::firstOrCreate([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1
                ]);
                $stock->increment('quantity', $product->qty);

                StockLedger::create([
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'outlet_id' => 1,
                    'reference_type' => 'opening',
                    'reference_id' => $product->id,
                    'in_qty' => $product->qty,
                    'out_qty' => 0,
                    'balance_qty' => $stock->quantity,
                    'date' => date('Y-m-d')
                ]);
            }

            // ========== Handle Combined Variants (color + size pair) ==========
            $variantsAddedCount = 0;
            $variantIndexes = $this->collectVariantIndexes(array_keys($columnMap));
            $lastColorName = null;
            $lastSizeName = null;

            foreach ($variantIndexes as $i) {
                $varColorName = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_color_name',
                    'variant_{i}_color',
                    'color_name_{i}',
                    'color_{i}',
                ]);
                $varSizeName = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_size_name',
                    'variant_{i}_size',
                    'size_name_{i}',
                    'size_{i}',
                ]);

                if (($varColorName === null || $varColorName === '') && $varSizeName !== null && $lastColorName !== null) {
                    $varColorName = $lastColorName;
                }
                if (($varSizeName === null || $varSizeName === '') && $varColorName !== null && $lastSizeName !== null) {
                    $varSizeName = $lastSizeName;
                }
                if ($varColorName !== null && $varColorName !== '') {
                    $lastColorName = $varColorName;
                }
                if ($varSizeName !== null && $varSizeName !== '') {
                    $lastSizeName = $varSizeName;
                }

                $varQtyRaw = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_qty',
                    'variant_{i}_quantity',
                    'variant_{i}_stock',
                    'qty_{i}',
                    'quantity_{i}',
                    'stock_{i}',
                    'variant_{i}_size_qty',
                ]);
                $varOutletPriceRaw = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_outlet_price',
                    'variant_{i}_outlet',
                    'variant_{i}_wholesale_price',
                    'variant_{i}_whole_sale_price',
                    'outlet_price_{i}',
                    'outlet_{i}',
                    'wholesale_price_{i}',
                    'whole_sale_price_{i}',
                ]);
                $varPriceRaw = $this->getVariantFieldValue($getValue, $i, [
                    'variant_{i}_price',
                    'variant_{i}_selling_price',
                    'price_{i}',
                    'selling_price_{i}',
                    'variant_{i}_size_price',
                ]);

                $created = $this->createCombinedVariant(
                    $product,
                    $varColorName,
                    $varSizeName,
                    $varPriceRaw !== null ? (float) $varPriceRaw : null,
                    $varOutletPriceRaw !== null ? (float) $varOutletPriceRaw : null,
                    $varQtyRaw !== null ? (int) $varQtyRaw : 0
                );

                if ($created) {
                    $variantsAddedCount++;
                }
            }
            
            // Old format fallback
            if ($variantsAddedCount === 0) {
                $colorName = $getValue('color_name');
                $sizeName = $getValue('size_name');
                $variantPrice = $getValue('variant_price');
                $variantOutletPrice = $getValue('variant_outlet_price');
                $variantQty = $getValue('variant_qty', 0);

                $this->createCombinedVariant(
                    $product,
                    $colorName,
                    $sizeName,
                    $variantPrice !== null && $variantPrice !== '' ? (float) $variantPrice : null,
                    $variantOutletPrice !== null && $variantOutletPrice !== '' ? (float) $variantOutletPrice : null,
                    (int) $variantQty
                );
            }

            DB::commit();
            return $product;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Collect variant indexes from headers/keys.
     *
     * @param array<int, string> $keys
     * @return array<int, int>
     */
    private function collectVariantIndexes(array $keys): array
    {
        $indexes = [];

        foreach ($keys as $key) {
            $normalized = strtolower(trim((string) $key));

            if (preg_match('/^variant_(\d+)_(color_name|color|colour|size_name|size|qty|quantity|stock|wholesale_price|whole_sale_price|price|selling_price|outlet_price|outlet|size_price|size_qty)$/', $normalized, $match)) {
                $indexes[(int) $match[1]] = true;
                continue;
            }

            if (preg_match('/^(color_name|color|colour|size_name|size|qty|quantity|stock|wholesale_price|whole_sale_price|price|selling_price|outlet_price|outlet)_(\d+)$/', $normalized, $match)) {
                $indexes[(int) $match[2]] = true;
            }
        }

        $indexes = array_keys($indexes);
        sort($indexes);

        return $indexes;
    }

    /**
     * Read first non-empty value from multiple indexed variant key patterns.
     */
    private function getVariantFieldValue(callable $getter, int $index, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            $key = str_replace('{i}', (string) $index, $pattern);
            $value = $getter($key, null);

            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function createCombinedVariant(
        Product $product,
        ?string $colorName,
        ?string $sizeName,
        ?float $variantPrice,
        ?float $variantOutletPrice,
        int $qty
    ): bool {
        $colorName = trim((string) $colorName);
        $sizeName = trim((string) $sizeName);

        if ($colorName === '' && $sizeName === '') {
            return false;
        }

        $colorId = null;
        if ($colorName !== '') {
            $color = Color::whereRaw('LOWER(name) = ?', [strtolower($colorName)])->first();
            if (!$color) {
                $color = Color::create(['name' => $colorName, 'status' => 1]);
            }
            $colorId = $color->id;
            $colorName = $color->name;
        }

        $sizeId = null;
        if ($sizeName !== '') {
            $size = Size::whereRaw('LOWER(name) = ?', [strtolower($sizeName)])->first();
            if (!$size) {
                $size = Size::create(['name' => $sizeName, 'status' => 1]);
            }
            $sizeId = $size->id;
            $sizeName = $size->name;
        }

        $existingVariant = ProductVariant::where('product_id', $product->id)
            ->where('color_id', $colorId)
            ->where('size_id', $sizeId)
            ->first();

        if ($existingVariant) {
            return false;
        }

        $productVariant = new ProductVariant();
        $productVariant->product_id = $product->id;
        $productVariant->color_id = $colorId;
        $productVariant->size_id = $sizeId;
        $productVariant->color = $colorName !== '' ? $colorName : null;
        $productVariant->size = $sizeName !== '' ? $sizeName : null;
        $productVariant->name = trim(implode(' ', array_filter([$colorName, $sizeName]))) ?: 'Default';
        // Keep same meaning as Product create form:
        // price = Outlet/Customer price, outlet_price = Whole Sale price.
        $productVariant->price = $variantPrice !== null ? max(0, $variantPrice) : (float) $product->price;
        $productVariant->outlet_price = $variantOutletPrice !== null ? max(0, $variantOutletPrice) : (float) $product->outlet_price;
        $productVariant->qty = max(0, $qty);
        $productVariant->save();

        $this->updateVariantStock($product, $productVariant, $productVariant->qty);

        return true;
    }

    // ========== Image Handle Method ==========
    private function handleImage($imageValue, $coordinate = null)
    {
        try {
            // If coordinate is provided, check embedded images map first
            if ($coordinate && isset($this->embeddedImages[$coordinate])) {
                return $this->embeddedImages[$coordinate];
            }

            // যদি URL হয়
            if (filter_var($imageValue, FILTER_VALIDATE_URL)) {
                return $this->downloadImage($imageValue);
            }
            
            // যদি local file path হয়
            if (file_exists($imageValue) && is_file($imageValue)) {
                $filename = 'product_' . uniqid() . '.' . File::extension($imageValue);
                $path = 'uploads/products/' . $filename;
                
                if (!Storage::disk('public')->exists('uploads/products')) {
                    Storage::disk('public')->makeDirectory('uploads/products');
                }
                
                Storage::disk('public')->put($path, File::get($imageValue));
                return $path;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Image Handle Error: ' . $e->getMessage());
            return null;
        }
    }

    private function downloadImage($url)
    {
        try {
            $contents = file_get_contents($url);
            if ($contents) {
                $ext = pathinfo($url, PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
                $path = 'uploads/products/' . $filename;
                
                if (!Storage::disk('public')->exists('uploads/products')) {
                    Storage::disk('public')->makeDirectory('uploads/products');
                }
                
                Storage::disk('public')->put($path, $contents);
                return $path;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Image Download Error: ' . $e->getMessage());
            return null;
        }
    }

    private function convertToBase64($filePath)
    {
        try {
            if (file_exists($filePath)) {
                $type = pathinfo($filePath, PATHINFO_EXTENSION);
                $data = file_get_contents($filePath);
                return 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        } catch (\Exception $e) {
            Log::error('Base64 Conversion Error: ' . $e->getMessage());
        }
        return $filePath;
    }

    private function columnLetterToIndex($column)
    {
        $column = strtoupper($column);
        $length = strlen($column);
        $index = 0;
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($column[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function indexToColumnLetter($index)
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + ord('A')) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }

    private function extractImagesFromExcel($filePath)
    {
        $images = [];
        $zip = new ZipArchive();
        if ($zip->open($filePath) === TRUE) {
            // 1. Get drawing relationships to find image IDs to file paths
            $drawingRels = [];
            if ($zip->locateName('xl/drawings/_rels/drawing1.xml.rels')) {
                $relsXml = simplexml_load_string($zip->getFromName('xl/drawings/_rels/drawing1.xml.rels'));
                foreach ($relsXml->Relationship as $rel) {
                    $drawingRels[(string)$rel['Id']] = (string)$rel['Target'];
                }
            }

            // 2. Get drawing XML to find anchor coordinates and their image IDs
            if ($zip->locateName('xl/drawings/drawing1.xml')) {
                $drawingXml = simplexml_load_string($zip->getFromName('xl/drawings/drawing1.xml'));
                // Register namespaces for xdr
                $drawingXml->registerXPathNamespace('xdr', 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing');
                $drawingXml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

                foreach (['twoCellAnchor', 'oneCellAnchor'] as $anchorType) {
                    foreach ($drawingXml->xpath('//xdr:' . $anchorType) as $anchor) {
                        try {
                            $xdr = $anchor->children('http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing');
                            if (!$xdr->from) continue;
                            
                            $col = (int)$xdr->from->col;
                            $row = (int)$xdr->from->row;
                            $colLetter = $this->indexToColumnLetter($col);
                            $coordinate = $colLetter . ($row + 1);

                            // Get blip ID
                            $blipId = null;
                            $a = $anchor->xpath('.//a:blip');
                            if (!empty($a)) {
                                $blipId = (string)$a[0]->attributes('r', true)->embed;
                            }

                            if ($blipId && isset($drawingRels[$blipId])) {
                                $targetPath = $drawingRels[$blipId];
                                if (strpos($targetPath, '../') === 0) {
                                    $targetPath = 'xl/' . str_replace('../', '', $targetPath);
                                }

                                if ($zip->locateName($targetPath)) {
                                    $contents = $zip->getFromName($targetPath);
                                    $ext = pathinfo($targetPath, PATHINFO_EXTENSION);
                                    $filename = 'excel_product_' . time() . '_' . uniqid() . '.' . $ext;
                                    $savePath = 'uploads/products/' . $filename;

                                    if (!Storage::disk('public')->exists('uploads/products')) {
                                        Storage::disk('public')->makeDirectory('uploads/products');
                                    }

                                    Storage::disk('public')->put($savePath, $contents);
                                    $images[$coordinate] = $savePath;
                                    // Log::info("Extracted {$anchorType} image for cell {$coordinate}: {$savePath}");
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Error processing {$anchorType}: " . $e->getMessage());
                        }
                    }
                }
            }
            $zip->close();
        }
        return $images;
    }
    private function updateVariantStock($product, $productVariant, $qty)
    {
        if ($qty > 0) {
            $stock = InventoryStock::firstOrCreate([
                'product_id' => $product->id,
                'variant_id' => $productVariant->id,
                'outlet_id' => 1
            ]);
            $stock->increment('quantity', $qty);

            StockLedger::create([
                'product_id' => $product->id,
                'variant_id' => $productVariant->id,
                'outlet_id' => 1,
                'reference_type' => 'opening',
                'reference_id' => $productVariant->id,
                'in_qty' => $qty,
                'out_qty' => 0,
                'balance_qty' => $stock->quantity,
                'date' => date('Y-m-d')
            ]);
        }
    }
}
