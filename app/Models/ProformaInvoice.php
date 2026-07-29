<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProformaInvoice extends Model
{
    use HasFactory;

    protected $table = 'proforma_invoices';

    protected $fillable = [
        'pi_no',
        'vendor_id',
        'rfq_id',
        'currency_id',
        'total_amount',
        'issue_date',
        'status',
        'attachment_path',
        'remarks',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'issue_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'proforma_invoice_id');
    }

    public function letterOfCredit()
    {
        return $this->hasOne(LetterOfCredit::class, 'proforma_invoice_id');
    }
}
