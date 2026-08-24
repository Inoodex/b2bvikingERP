<?php

namespace App\DataTables;

use App\Models\LetterOfCredit;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LcDataTable extends \Yajra\DataTables\Services\DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('lc_no', function ($query) {
                return '<a href="' . route('admin.letters-of-credit.show', $query->id) . '" class="font-weight-bold text-primary">' . $query->lc_no . '</a>';
            })
            ->editColumn('vendor_name', function ($query) {
                return $query->vendor ? $query->vendor->shop_name : 'N/A';
            })
            ->editColumn('issuing_bank', function ($query) {
                return $query->issuing_bank ?? 'N/A';
            })
            ->editColumn('amount', function ($query) {
                $symbol = $query->currency ? ($query->currency->symbol ?? $query->currency->code) : ($query->vendor?->currency ? ($query->vendor->currency->symbol ?? $query->vendor->currency->code) : 'kr.');
                return $symbol . ' ' . number_format($query->amount, 2);
            })
            ->editColumn('margin_percent', function ($query) {
                return ($query->margin_percent ?? 0) . '%';
            })
            ->editColumn('expenses_total', function ($query) {
                return 'kr. ' . number_format($query->total_expenses, 2);
            })
            ->editColumn('status', function ($query) {
                $class = match ($query->status) {
                    'open' => 'badge-success',
                    'amended' => 'badge-info',
                    'closed' => 'badge-secondary',
                    'cancelled' => 'badge-danger',
                    default => 'badge-light',
                };
                return '<span class="badge ' . $class . '">' . ucfirst($query->status) . '</span>';
            })
            ->editColumn('expiry_date', function ($query) {
                return $query->expiry_date ? $query->expiry_date->format('d M, Y') : 'N/A';
            })
            ->addColumn('action', function ($query) {
                $btn = '<a href="' . route('admin.letters-of-credit.show', $query->id) . '" class="btn btn-info btn-sm mr-1" title="View LC Details"><i class="fas fa-eye"></i></a>';
                return $btn;
            })
            ->rawColumns(['lc_no', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(LetterOfCredit $model): QueryBuilder
    {
        return $model->newQuery()->with(['vendor', 'currency', 'expenses', 'amendments'])->orderByDesc('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('lc-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->stateSave(true)
            ->responsive(true)
            ->autoWidth(false)
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::computed('lc_no')->title('LC Number'),
            Column::computed('vendor_name')->title('Supplier / Vendor'),
            Column::computed('issuing_bank')->title('Issuing Bank'),
            Column::make('amount')->title('LC Amount'),
            Column::make('margin_percent')->title('Margin %'),
            Column::computed('expenses_total')->title('Total Expenses'),
            Column::make('expiry_date')->title('Expiry Date'),
            Column::computed('status')->title('Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }
}
