<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComparisonStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'cs_no',
        'rfq_id',
        'recommended_vendor_id',
        'status',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function recommendedVendor()
    {
        return $this->belongsTo(Vendor::class, 'recommended_vendor_id');
    }

    public function items()
    {
        return $this->hasMany(ComparisonStatementItem::class);
    }
}
