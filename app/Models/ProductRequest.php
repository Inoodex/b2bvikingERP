<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'user_id',
        'status',
        'required_days',
        'total_qty',
        'total_amount',
        'order_id',
        'note',
        'admin_note'
    ];

    protected $casts = [
        'pi_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()

    {
        return $this->hasMany(ProductRequestItem::class);
    }
}
