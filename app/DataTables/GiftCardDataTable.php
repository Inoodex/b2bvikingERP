<?php

namespace App\DataTables;

use App\Models\GiftCard;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GiftCardDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $showBtn = '<a href="' . route('admin.gift-cards.show', $query->id) . '" class="btn btn-sm btn-info shadow-sm mr-1" title="View Ledger Transactions"><i class="fas fa-eye"></i></a>';
                $deleteBtn = '<a href="' . route('admin.gift-cards.destroy', $query->id) . '" class="btn btn-sm btn-danger shadow-sm delete-item" title="Delete"><i class="fas fa-trash"></i></a>';
                return $showBtn . $deleteBtn;
            })
            ->addColumn('code_badge', function ($query) {
                return '<span class="badge badge-dark px-3 py-1 font-weight-bold" style="font-family: monospace; font-size: 0.9rem; letter-spacing: 1px;"><i class="fas fa-credit-card text-warning mr-1"></i>' . e($query->code) . '</span>';
            })
            ->addColumn('initial_val', function ($query) {
                $currencySymbol = $query->currency?->symbol ?? 'kr.';
                return '<strong>' . $currencySymbol . ' ' . number_format($query->initial_value, 2) . '</strong>';
            })
            ->addColumn('current_balance', function ($query) {
                $currencySymbol = $query->currency?->symbol ?? 'kr.';
                $class = $query->balance > 0 ? 'badge-success' : 'badge-secondary';
                return '<span class="badge ' . $class . ' px-3 py-1 font-weight-bold">' . $currencySymbol . ' ' . number_format($query->balance, 2) . '</span>';
            })
            ->addColumn('expiry_date', function ($query) {
                if (!$query->expires_at) {
                    return '<span class="badge badge-success">No Expiry</span>';
                }
                $isExpired = $query->expires_at->isPast();
                $class = $isExpired ? 'badge-danger' : 'badge-light border text-dark';
                return '<span class="badge ' . $class . '">' . $query->expires_at->format('d M, Y') . '</span>';
            })
            ->addColumn('status', function ($query) {
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-giftcard-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->rawColumns(['action', 'code_badge', 'initial_val', 'current_balance', 'expiry_date', 'status'])
            ->setRowId('id');
    }

    public function query(GiftCard $model)
    {
        return $model->newQuery()->with('currency')->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('giftcard-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('print'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->width(50),
            Column::make('code_badge')->title('Gift Card Number'),
            Column::make('initial_val')->title('Initial Value'),
            Column::make('current_balance')->title('Remaining Balance'),
            Column::make('expiry_date')->title('Expiration Date'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'GiftCards_' . date('YmdHis');
    }
}
