<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'invoice_no',
        'vendor_id',
        'user_id',
        'date',
        'total_amount',
        'note',
        'status',
        'shipping_method',
        'booking_id',
        'material_cost',
        'transport_cost',
        'tax',
        'invoice_attachment',
        'paid_amount',
        'due_amount',
        'payment_status',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'material_cost' => 'float',
        'transport_cost' => 'float',
        'tax' => 'float',
        'paid_amount' => 'float',
        'due_amount' => 'float',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function attachments()
    {
        return $this->hasMany(PurchaseAttachment::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }
}
