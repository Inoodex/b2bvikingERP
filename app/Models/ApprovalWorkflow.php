<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document_type',
        'model_type',
        'min_amount',
        'max_amount',
        'status',
    ];

    protected $casts = [
        'min_amount' => 'double',
        'max_amount' => 'double',
        'status' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
