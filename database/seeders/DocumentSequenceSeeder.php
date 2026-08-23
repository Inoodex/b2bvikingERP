<?php

namespace Database\Seeders;

use App\Models\DocumentSequence;
use Illuminate\Database\Seeder;

class DocumentSequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sequences = [
            [
                'model_type' => 'WebOrder',
                'prefix' => 'ORD-',
                'suffix' => null,
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ],
            [
                'model_type' => 'OutletOrder',
                'prefix' => 'DS-',
                'suffix' => null,
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ],
            [
                'model_type' => 'SalesOrder',
                'prefix' => 'SO-',
                'suffix' => null,
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ],
            [
                'model_type' => 'SalesQuotation',
                'prefix' => 'SQ-',
                'suffix' => null,
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ],
            [
                'model_type' => 'SalesInvoice',
                'prefix' => 'INV-',
                'suffix' => null,
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ],
            [
                'model_type' => 'DeliveryOrder',
                'prefix' => 'DO-',
                'suffix' => null,
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ],
            [
                'model_type' => 'CreditNote',
                'prefix' => 'CN-',
                'suffix' => null,
                'padding' => 4,
                'next_number' => 1,
                'reset_policy' => 'yearly',
                'include_date' => true,
                'date_format' => 'Ym',
            ],
        ];

        foreach ($sequences as $seq) {
            DocumentSequence::updateOrCreate(
                ['model_type' => $seq['model_type']],
                $seq
            );
        }
    }
}
