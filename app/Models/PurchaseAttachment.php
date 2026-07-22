<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseAttachment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'purchase_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
