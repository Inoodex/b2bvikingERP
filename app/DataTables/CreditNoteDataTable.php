<?php

namespace App\DataTables;

use App\Models\CreditNote;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CreditNoteDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('credit_note_no', function ($query) {
                return '<a href="' . route('admin.credit-notes.show', $query->id) . '" class="font-weight-bold text-primary">' . e($query->credit_note_no) . '</a>';
            })
            ->addColumn('sales_return_no', function ($query) {
                return $query->salesReturn ? '<a href="' . route('admin.sales-returns.show', $query->sales_return_id) . '">' . e($query->salesReturn->return_no) . '</a>' : 'Direct';
            })
            ->addColumn('customer', function ($query) {
                return $query->customer ? e($query->customer->outlet_name ?: $query->customer->name) : 'General Customer';
            })
            ->addColumn('amount', function ($query) {
                return 'kr. ' . number_format((float)$query->amount, 2);
            })
            ->addColumn('settlement_status', function ($query) {
                if ($query->settlement_status === 'settled') {
                    return '<span class="badge badge-success">Settled</span>';
                } elseif ($query->settlement_status === 'partial') {
                    return '<span class="badge badge-info">Partial</span>';
                }
                return '<span class="badge badge-warning">Unsettled</span>';
            })
            ->addColumn('action', function ($query) {
                $view = '<a href="' . route('admin.credit-notes.show', $query->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>';
                $pdf = '<a href="' . route('admin.credit-notes.pdf', $query->id) . '" target="_blank" class="btn btn-secondary btn-sm ml-1" title="PDF"><i class="fas fa-file-pdf"></i></a>';
                return $view . $pdf;
            })
            ->rawColumns(['action', 'credit_note_no', 'sales_return_no', 'settlement_status'])
            ->setRowId('id');
    }

    public function query(CreditNote $model)
    {
        return $model->newQuery()->with(['salesReturn', 'customer']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('credit-note-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('credit_note_no')->title('Credit Note No')->addClass('text-center'),
            Column::computed('sales_return_no')->title('Return Ref')->addClass('text-center'),
            Column::computed('customer')->title('Customer')->addClass('text-center'),
            Column::make('amount')->title('Total Value')->addClass('text-center'),
            Column::make('settlement_status')->title('Status')->addClass('text-center'),
            Column::computed('action')->title('Action')->addClass('text-center')
                ->exportable(false)
                ->printable(false)
                ->width(100),
        ];
    }

    protected function filename(): string
    {
        return 'CreditNote_' . date('YmdHis');
    }
}
