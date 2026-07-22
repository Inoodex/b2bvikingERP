<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = [
        'issue_no',
        'product_request_id',
        'order_id',
        'outlet_id',
        'status',
        'total_qty',
        'note',
        'invoice_path'
    ];

    public function productRequest()
    {
        return $this->belongsTo(ProductRequest::class);
    }

    public function outlet()
    {
        return $this->belongsTo(User::class, 'outlet_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(IssueItem::class);
    }

    public function returns()
    {
        return $this->hasMany(IssueReturn::class);
    }
}
