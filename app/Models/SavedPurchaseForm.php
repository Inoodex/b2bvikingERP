<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedPurchaseForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'user_id',
        'status',
        'total_qty',
        'total_amount',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SavedPurchaseFormItem::class);
    }
}

