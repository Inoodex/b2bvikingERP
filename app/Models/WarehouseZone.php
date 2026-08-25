<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'name',
        'type',
        'description',
        'status',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function bins()
    {
        return $this->hasMany(WarehouseBin::class, 'zone_id');
    }
}
