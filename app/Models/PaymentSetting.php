<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $table = 'payment_settings';

    protected $fillable = [
        'key',
        'name',
        'status',
        'mode',
        'country_name',
        'currency_name',
        'currency_rate',
        'client_id',
        'client_secret',
        'instructions',
        'deposit_account_id',
        'additional_config',
    ];

    protected $casts = [
        'currency_rate'     => 'decimal:4',
        'additional_config' => 'array',
    ];

    /**
     * Relationship with Chart of Account for COD cashier deposit.
     */
    public function depositAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'deposit_account_id');
    }

    /**
     * Check if this gateway is currently enabled.
     */
    public function isEnabled(): bool
    {
        return strtolower((string) $this->status) === 'enable';
    }

    /**
     * Fetch a specific gateway setting by key.
     */
    public static function getGateway(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /**
     * Check if a specific gateway is enabled by key.
     */
    public static function isGatewayEnabled(string $key): bool
    {
        $gateway = static::getGateway($key);

        return $gateway ? $gateway->isEnabled() : false;
    }

    /**
     * Fetch all active gateways keyed by key.
     */
    public static function getActiveGateways(): \Illuminate\Support\Collection
    {
        return static::where('status', 'enable')->get()->keyBy('key');
    }
}
