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
        $sequence = static::firstOrCreate(
            ['model_type' => $modelType],
            [
                'prefix' => strtoupper(substr($modelType, 0, 3)) . '-',
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ]
        );

        $number = str_pad((string) $sequence->next_number, $sequence->padding, '0', STR_PAD_LEFT);
        $dateStr = $sequence->include_date ? date($sequence->date_format) . '-' : '';
        $docNo = ($sequence->prefix ?? '') . $dateStr . $number . ($sequence->suffix ?? '');

        $sequence->increment('next_number');

        return $docNo;
    }
}
