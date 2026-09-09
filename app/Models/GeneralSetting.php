<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = [
        'site_name',
        'contact_email',
        'phone',
        'address',
        'site_logo',
        'currency_name',
        'currency_icon',
        'currency_rate',
        'base_currency_name',
        'base_currency_icon',
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'feature_toggles',
        'paypal_enabled',
        'paypal_mode',
        'paypal_client_id',
        'paypal_client_secret',
        'paypal_currency',
        'cod_enabled',
        'cod_max_limit',
        'cod_instructions',
        'cod_deposit_account_id',
    ];

    protected $casts = [
        'feature_toggles' => 'array',
        'paypal_enabled'  => 'boolean',
        'cod_enabled'     => 'boolean',
        'cod_max_limit'   => 'decimal:2',
    ];

    public function codDepositAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cod_deposit_account_id');
    }
}
