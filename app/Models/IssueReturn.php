<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueReturn extends Model
{
    protected $fillable = [
        'return_no',
        'issue_id',
        'order_id',
        'outlet_id',
        'refund_amount',
        'note',
        'status',
        'approved_by'
    ];

    protected $casts = [
        'refund_amount' => 'float',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function outlet()
    {
        return $this->belongsTo(User::class, 'outlet_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(IssueReturnItem::class);
    }
}
