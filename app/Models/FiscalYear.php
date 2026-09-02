<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    use HasFactory;

    protected $table = 'fiscal_years';

    protected $fillable = [
        'company_id',
        'name',
        'start_date',
        'end_date',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'closed_at'  => 'datetime',
        'is_closed'  => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function scopeActiveForDate($query, $date)
    {
        return $query->where('is_closed', false)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }
}
