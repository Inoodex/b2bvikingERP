<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LcAmendment extends Model
{
    use HasFactory;

    protected $table = 'lc_amendments';

    protected $fillable = [
        'lc_id',
        'amendment_no',
        'change_details',
        'amended_date',
    ];

    protected $casts = [
        'amended_date' => 'date',
    ];

    public function letterOfCredit()
    {
        return $this->belongsTo(LetterOfCredit::class, 'lc_id');
    }
}
