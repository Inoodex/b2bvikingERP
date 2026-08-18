<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id',
        'entry_no',
        'entry_date',
        'reference_type',
        'reference_id',
        'currency_id',
        'narration',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function journalable()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
