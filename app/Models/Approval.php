<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'approval_step_id',
        'user_id',
        'status',
        'comments',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function step()
    {
        return $this->belongsTo(ApprovalStep::class, 'approval_step_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
