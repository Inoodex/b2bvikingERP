<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_no',
        'from_outlet_id',
        'to_outlet_id',
        'requested_by',
        'dispatched_by',
        'received_by',
        'status',
        'transfer_date',
        'dispatched_at',
        'received_at',
        'challan_no',
        'vehicle_no',
        'driver_name',
        'driver_phone',
        'note',
        'total_items_count',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
        'total_items_count' => 'integer',
    ];

    public function fromOutlet()
    {
        return $this->belongsTo(Outlet::class, 'from_outlet_id');
    }

    public function toOutlet()
    {
        return $this->belongsTo(Outlet::class, 'to_outlet_id');
    }

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function dispatchedByUser()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
