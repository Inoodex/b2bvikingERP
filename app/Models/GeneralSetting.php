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
    ];}
