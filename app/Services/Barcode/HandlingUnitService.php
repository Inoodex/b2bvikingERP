<?php

declare(strict_types=1);

namespace App\Services\Barcode;

use App\Models\HandlingUnit;
use App\Models\HandlingUnitItem;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HandlingUnitService
{
    public function __construct(
        protected Gs1BarcodeService $gs1BarcodeService
    ) {}

    /**
     * Create a new Handling Unit (Carton, Box, Pallet, Container) with unique GS1-compliant barcode.
     *
     * @param  array<string, mixed>  $data
     */
    public function createHandlingUnit(array $data): HandlingUnit
    {
        return DB::transaction(function () use ($data) {
            $packagingTypeId = (int) ($data['packaging_type_id'] ?? 1);
            $packagingType = PackagingType::findOrFail($packagingTypeId);

            $gpcCode = $data['gpc_code'] ?? null;
            if (!$gpcCode && !empty($data['category_id'])) {
                $category = \App\Models\Category::find($data['category_id']);
                $gpcCode = $this->gs1BarcodeService->resolveGpcCode($category);
            }

            $huCode = $data['hu_code'] ?? null;
            if (empty($huCode)) {
                $huCode = $this->gs1BarcodeService->generateHandlingUnitBarcode($packagingType->code, $gpcCode);
            }

            $unit = HandlingUnit::create([
                'hu_code' => $huCode,
                'packaging_type_id' => $packagingType->id,
                'parent_handling_unit_id' => !empty($data['parent_handling_unit_id']) ? (int) $data['parent_handling_unit_id'] : null,
                'warehouse_id' => !empty($data['warehouse_id']) ? (int) $data['warehouse_id'] : null,
                'status' => $data['status'] ?? 'packed',
                'gross_weight' => isset($data['gross_weight']) ? (float) $data['gross_weight'] : $packagingType->tare_weight,
                'batch_no' => $data['batch_no'] ?? null,
                'notes' => $data['notes'] ?? null,
                'packed_by' => auth()->id() ?? ($data['packed_by'] ?? null),
                'packed_at' => now(),
            ]);

            // Pack initial items if provided
            if (!empty($data['items']) && is_array($data['items'])) {
                $this->packItemsIntoUnit($unit, $data['items']);
            }

            // Pack child units if provided
            if (!empty($data['child_unit_ids']) && is_array($data['child_unit_ids'])) {
                foreach ($data['child_unit_ids'] as $childId) {
                    $child = HandlingUnit::find($childId);
                    if ($child) {
                        $this->nestChildUnit($unit, $child);
                    }
                }
            }

            $unit->recalculateTotalQuantity();

            return $unit->fresh(['packagingType', 'parentUnit', 'childUnits', 'items.product', 'items.variant']);
        });
    }

    /**
     * Pack products and variants into a handling unit.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function packItemsIntoUnit(HandlingUnit $unit, array $items): HandlingUnit
    {
        return DB::transaction(function () use ($unit, $items) {
            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $variantId = !empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $batchNo = $item['batch_no'] ?? $unit->batch_no;
                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : null;

                if ($productId <= 0) {
                    continue;
                }

                $product = Product::findOrFail($productId);
                $variant = $variantId ? ProductVariant::find($variantId) : null;
                $availableStock = (int) ($variant ? ($variant->total_stock ?? $variant->qty ?? 0) : ($product->total_stock ?? $product->qty ?? 0));
                $itemName = $product->name . ($variant ? " ({$variant->name})" : '');

                if ($availableStock <= 0) {
                    throw new InvalidArgumentException("Cannot pack out-of-stock item '{$itemName}'. Current available warehouse stock is 0.");
                }

                if ($quantity > $availableStock) {
                    throw new InvalidArgumentException("Requested packing quantity ({$quantity}) for '{$itemName}' exceeds available warehouse stock ({$availableStock}).");
                }

                // If unit price not provided, fetch from product/variant
                if ($unitPrice === null) {
                    $unitPrice = $variant ? (float) ($variant->price > 0 ? $variant->price : $product->price) : (float) $product->price;
                }

                HandlingUnitItem::create([
                    'handling_unit_id' => $unit->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'batch_no' => $batchNo,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);
            }

            $unit->recalculateTotalQuantity();

            // Bubble quantity upwards to parent units
            $parent = $unit->parentUnit;
            while ($parent) {
                $parent->recalculateTotalQuantity();
                $parent = $parent->parentUnit;
            }

            return $unit;
        });
    }

    /**
     * Nest a child handling unit into a parent handling unit (Arbitrary N-Tier tree).
     */
    public function nestChildUnit(HandlingUnit $parentUnit, HandlingUnit $childUnit): void
    {
        if ($parentUnit->id === $childUnit->id) {
            throw new InvalidArgumentException("A handling unit cannot be a parent of itself.");
        }

        // Circular hierarchy safety check: ensure childUnit is not an ancestor of parentUnit
        $ancestorId = $parentUnit->parent_handling_unit_id ?? HandlingUnit::where('id', $parentUnit->id)->value('parent_handling_unit_id');
        while ($ancestorId) {
            if ($ancestorId === $childUnit->id) {
                throw new InvalidArgumentException("Circular packaging hierarchy detected.");
            }
            $ancestorId = HandlingUnit::where('id', $ancestorId)->value('parent_handling_unit_id');
        }

        $childUnit->update(['parent_handling_unit_id' => $parentUnit->id]);
        $parentUnit->unsetRelation('childUnits');
        $parentUnit->recalculateTotalQuantity();
    }

    /**
     * Find handling unit by its barcode (HU Code).
     */
    public function getByHuCode(string $huCode): ?HandlingUnit
    {
        return HandlingUnit::with([
            'packagingType',
            'parentUnit.packagingType',
            'childUnits.packagingType',
            'childUnits.items.product',
            'items.product.category',
            'items.variant',
        ])->where('hu_code', trim($huCode))->first();
    }

    /**
     * Search Handling Units for Warehouse & Packaging Studio.
     *
     * @return Collection<int, HandlingUnit>
     */
    public function searchUnits(string $query = '', ?int $packagingTypeId = null, ?string $status = null, int $limit = 50): Collection
    {
        $q = HandlingUnit::query()
            ->with(['packagingType', 'parentUnit.packagingType', 'items.product', 'items.variant'])
            ->withCount('childUnits')
            ->latest('id');

        if (!empty($query)) {
            $q->where(function ($sub) use ($query) {
                $sub->where('hu_code', 'like', "%{$query}%")
                    ->orWhere('batch_no', 'like', "%{$query}%")
                    ->orWhereHas('items.product', function ($pq) use ($query) {
                        $pq->where('name', 'like', "%{$query}%")
                            ->orWhere('sku', 'like', "%{$query}%");
                    });
            });
        }

        if ($packagingTypeId) {
            $q->where('packaging_type_id', $packagingTypeId);
        }

        if ($status) {
            $q->where('status', $status);
        }

        return $q->limit($limit)->get();
    }

    /**
     * Scan-to-Receive Handling Unit in Warehouse (1-Scan Bulk Inward).
     */
    public function receiveUnitByBarcode(string $huCode, ?int $warehouseId = null): HandlingUnit
    {
        $unit = $this->getByHuCode($huCode);
        if (!$unit) {
            throw new InvalidArgumentException("Handling Unit with barcode [{$huCode}] not found.");
        }

        $unit->update([
            'status' => 'received',
            'warehouse_id' => $warehouseId ?? $unit->warehouse_id,
        ]);

        $this->markChildrenStatus($unit, 'received', $warehouseId);

        return $unit;
    }

    /**
     * Scan-to-Ship Handling Unit from Warehouse (1-Scan Bulk Dispatch).
     */
    public function shipUnitByBarcode(string $huCode): HandlingUnit
    {
        $unit = $this->getByHuCode($huCode);
        if (!$unit) {
            throw new InvalidArgumentException("Handling Unit with barcode [{$huCode}] not found.");
        }

        $unit->update(['status' => 'shipped']);
        $this->markChildrenStatus($unit, 'shipped');

        return $unit;
    }

    /**
     * Recursively update status of all nested child units in hierarchy.
     */
    protected function markChildrenStatus(HandlingUnit $unit, string $status, ?int $warehouseId = null): void
    {
        foreach ($unit->childUnits()->get() as $child) {
            $child->update([
                'status' => $status,
                'warehouse_id' => $warehouseId ?? $child->warehouse_id,
            ]);
            $this->markChildrenStatus($child, $status, $warehouseId);
        }
    }
}
