<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseBin extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'name',
        'barcode',
        'description',
        'status',
    ];

    public function zone()
    {
        return $this->belongsTo(WarehouseZone::class);
    }
}
