<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfq extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfq_no',
        'source_type',
        'source_id',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function source()
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(RfqVendor::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class);
    }
}
