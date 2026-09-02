<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'company_id',
        'account_code',
        'account_name',
        'account_type',
        'normal_balance',
        'parent_id',
        'is_group',
        'is_active',
    ];

    protected $casts = [
        'is_group'  => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePostingAccounts(Builder $query): Builder
    {
        return $query->where('is_group', false)->where('is_active', true);
    }

    public function getBalanceAttribute(): float
    {
        $debit = (float) $this->journalLines()->sum('debit');
        $credit = (float) $this->journalLines()->sum('credit');

        if ($this->normal_balance === 'debit') {
            return round($debit - $credit, 2);
        }

        return round($credit - $debit, 2);
    }

    public function isSystemProtected(): bool
    {
        $protectedCodes = ['1010', '1020', '1030', '1050', '2010', '2020', '2030', '3010', '3020', '4010', '5010'];
        return in_array($this->account_code, $protectedCodes);
    }
}
