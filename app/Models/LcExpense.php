<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LcExpense extends Model
{
    use HasFactory;

    protected $table = 'lc_expenses';

    protected $fillable = [
        'lc_id',
        'cost_element',
        'amount',
        'currency_id',
        'goes_to_unit_cost',
        'gl_account_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'goes_to_unit_cost' => 'boolean',
    ];

    public function letterOfCredit()
    {
        return $this->belongsTo(LetterOfCredit::class, 'lc_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
