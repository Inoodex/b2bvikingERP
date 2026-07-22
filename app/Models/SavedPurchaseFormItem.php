<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPurchaseFormItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'saved_purchase_form_id',
        'product_id',
        'variant_id',
        'qty',
        'unit_price',
        'subtotal',
    ];

    public function savedPurchaseForm()
    {
        return $this->belongsTo(SavedPurchaseForm::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}

