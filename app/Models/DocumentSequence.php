<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'prefix',
        'suffix',
        'padding',
        'next_number',
        'reset_policy',
        'include_date',
        'date_format',
    ];

    protected $casts = [
        'padding' => 'integer',
        'next_number' => 'integer',
        'include_date' => 'boolean',
    ];

    /**
     * Generate the next document number for a given model type.
     */
    public static function generateNext(string $modelType): string
    {
        $defaultPrefixes = [
            'WebOrder' => 'ORD-',
            'OutletOrder' => 'DS-',
            'SalesOrder' => 'SO-',
            'SalesQuotation' => 'SQ-',
            'SalesInvoice' => 'INV-',
            'DeliveryOrder' => 'DO-',
            'CreditNote' => 'CN-',
        ];

        $sequence = static::firstOrCreate(
            ['model_type' => $modelType],
            [
                'prefix' => $defaultPrefixes[$modelType] ?? (strtoupper(substr($modelType, 0, 3)) . '-'),
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ]
        );

        $modelClassMap = [
            'DeliveryOrder'  => [\App\Models\DeliveryOrder::class, 'delivery_no'],
            'SalesQuotation' => [\App\Models\SalesQuotation::class, 'quotation_no'],
            'SalesInvoice'   => [\App\Models\SalesInvoice::class, 'invoice_no'],
            'CreditNote'     => [\App\Models\CreditNote::class, 'credit_note_no'],
            'SalesOrder'     => [\App\Models\Order::class, 'order_no'],
            'WebOrder'       => [\App\Models\Order::class, 'order_no'],
            'OutletOrder'    => [\App\Models\Order::class, 'order_no'],
        ];

        do {
            $number = str_pad((string) $sequence->next_number, $sequence->padding, '0', STR_PAD_LEFT);
            $dateStr = $sequence->include_date ? date($sequence->date_format) . '-' : '';
            $docNo = ($sequence->prefix ?? '') . $dateStr . $number . ($sequence->suffix ?? '');

            $sequence->increment('next_number');

            $exists = false;
            if (isset($modelClassMap[$modelType])) {
                [$modelClass, $column] = $modelClassMap[$modelType];
                if (class_exists($modelClass)) {
                    $query = method_exists($modelClass, 'withTrashed')
                        ? $modelClass::withTrashed()
                        : $modelClass::query();
                    $exists = $query->where($column, $docNo)->exists();
                }
            }
        } while ($exists);

        return $docNo;
    }

    /**
     * Generate the next document number for an order based on the creator's role.
     */
    public static function generateForOrder(?User $user = null): string
    {
        if ($user && ($user->hasRole('Outlet User') || $user->hasRole('Outlet'))) {
            return static::generateNext('OutletOrder');
        }

        return static::generateNext('WebOrder');
    }
}
