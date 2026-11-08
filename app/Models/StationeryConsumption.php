<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StationeryConsumption extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'issue_no',
        'department_id',
        'department_name',
        'issued_to',
        'issue_date',
        'total_cost',
        'created_by',
        'purpose_notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total_cost' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(StationeryConsumptionItem::class, 'stationery_consumption_id');
    }

    public function journalEntry()
    {
        return $this->morphOne(JournalEntry::class, 'reference');
    }

    public function getDisplayDepartmentAttribute(): string
    {
        if ($this->department && !empty($this->department->name)) {
            return $this->department->name;
        }

        return $this->department_name ?: 'General / Operations';
    }
}

