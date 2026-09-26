<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesQuotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_no',
        'customer_id',
        'template_id',
        'currency_id',
        'exchange_rate',
        'tax_id',
        'incoterm',
        'valid_until',
        'status',
        'subtotal_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'created_by',
        'reminder_sent',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'exchange_rate' => 'float',
        'subtotal_amount' => 'float',
        'tax_amount' => 'float',
        'discount_amount' => 'float',
        'total_amount' => 'float',
        'reminder_sent' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesQuotationItem::class, 'sales_quotation_id');
    }

    /**
     * Check if quotation was created for a generic walk-in / inquiry prospect
     */
    public function getIsProspectAttribute(): bool
    {
        if ($this->customer && $this->customer->email === 'prospect@b2bviking.local') {
            return true;
        }
        return (bool) ($this->notes && str_contains($this->notes, '[PROSPECT_LEAD:'));
    }

    /**
     * Get display name for the buyer (prospect shop/name or registered customer)
     */
    public function getBuyerDisplayNameAttribute(): string
    {
        if ($this->notes && preg_match('/\[PROSPECT_LEAD:\s*Name:\s*([^|\]]+)(?:\|[^\]]+)?\]/', $this->notes, $matches)) {
            $parsed = trim($matches[1]);
            if (!empty($parsed)) {
                return $parsed;
            }
        }
        return $this->customer?->name ?? $this->customer?->outlet_name ?? 'Valued Prospective Buyer';
    }

    /**
     * Get buyer contact phone number
     */
    public function getBuyerPhoneAttribute(): ?string
    {
        if ($this->notes && preg_match('/\[PROSPECT_LEAD:[^|]*\|\s*Phone:\s*([^\]]+)\]/', $this->notes, $matches)) {
            $parsed = trim($matches[1]);
            if (!empty($parsed)) {
                return $parsed;
            }
        }
        return $this->customer?->phone;
    }

    /**
     * Get clean notes without the internal prospect lead tag
     */
    public function getCleanNotesAttribute(): ?string
    {
        if (!$this->notes) {
            return null;
        }
        $cleaned = trim(preg_replace('/\[PROSPECT_LEAD:[^\]]+\]\s*/', '', $this->notes));
        return $cleaned !== '' ? $cleaned : null;
    }
}

