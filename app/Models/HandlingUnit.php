<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HandlingUnit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hu_code',
        'packaging_type_id',
        'parent_handling_unit_id',
        'warehouse_id',
        'status',
        'total_quantity',
        'gross_weight',
        'batch_no',
        'notes',
        'packed_by',
        'packed_at',
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'gross_weight' => 'decimal:3',
        'packed_at' => 'datetime',
    ];

    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class, 'packaging_type_id');
    }

    /**
     * Parent container/carton (Self-referencing recursive relation).
     */
    public function parentUnit(): BelongsTo
    {
        return $this->belongsTo(HandlingUnit::class, 'parent_handling_unit_id');
    }

    /**
     * Nested child boxes/cartons (Self-referencing recursive relation).
     */
    public function childUnits(): HasMany
    {
        return $this->hasMany(HandlingUnit::class, 'parent_handling_unit_id')->with(['packagingType', 'items.product', 'items.variant']);
    }

    /**
     * Direct items packed into this handling unit.
     */
    public function items(): HasMany
    {
        return $this->hasMany(HandlingUnitItem::class)->with(['product', 'variant']);
    }

    public function packer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    /**
     * Recalculate total items count (direct items + all nested child handling units).
     */
    public function recalculateTotalQuantity(): int
    {
        $directQuantity = (int) $this->items()->sum('quantity');
        $childQuantity = 0;

        $children = $this->childUnits()->get();
        foreach ($children as $child) {
            $childQuantity += $child->recalculateTotalQuantity();
        }

        $total = $directQuantity + $childQuantity;
        $this->updateQuietly(['total_quantity' => $total]);

        return $total;
    }

    /**
     * Collect all products and variants contained in this handling unit and its nested children.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFlattenedContents(): array
    {
        $contents = [];

        foreach ($this->items()->get() as $item) {
            $contents[] = [
                'type' => 'direct',
                'handling_unit_code' => $this->hu_code,
                'packaging_type' => $this->packagingType?->name ?? 'Package',
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? 'Unknown',
                'sku' => $item->variant?->sku ?? $item->product?->sku ?? '',
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant?->name,
                'quantity' => $item->quantity,
                'batch_no' => $item->batch_no ?? $this->batch_no,
            ];
        }

        $children = $this->childUnits()->get();
        foreach ($children as $child) {
            $childContents = $child->getFlattenedContents();
            foreach ($childContents as $cc) {
                $contents[] = $cc;
            }
        }

        return $contents;
    }
}
