<?php

namespace App\DataTables;

use App\Models\PurchasePayment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchasePaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                $viewBtn = '<a href="' . route('admin.purchase-payments.show', $row->id) . '" class="btn btn-sm btn-info mr-1" title="View Voucher Details"><i class="fas fa-eye"></i> View</a>';
                $pdfBtn = '<a href="' . route('admin.purchase-payments.pdf', $row->id) . '" class="btn btn-sm btn-danger mr-1" target="_blank" title="Print Payment Voucher Slip"><i class="fas fa-file-pdf"></i> PDF</a>';
                return $viewBtn . $pdfBtn;
            })
            ->editColumn('payment_date', function ($row) {
                return $row->payment_date ? $row->payment_date->format('d M Y') : $row->created_at->format('d M Y');
            })
            ->editColumn('payment_method', function ($row) {
                return ucfirst(str_replace('_', ' ', $row->payment_method));
            })
            ->editColumn('amount', function ($row) {
                $symbol = $row->currency ? $row->currency->symbol : (getSettings()->currency_icon ?? 'Kr.');
                return $symbol . ' ' . number_format($row->amount, 2);
            })
            ->editColumn('base_amount', function ($row) {
                return number_format($row->base_amount, 2);
            })
            ->addColumn('vendor_name', function ($row) {
                return $row->vendor ? $row->vendor->name : 'N/A';
            })
            ->addColumn('po_no', function ($row) {
                return $row->purchase ? $row->purchase->po_no : 'N/A';
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PurchasePayment $model): QueryBuilder
    {
        return $model->newQuery()->with(['purchase', 'vendor', 'currency']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('purchasepayment-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'desc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->visible(false),
            Column::make('payment_no')->title('Voucher No'),
            Column::make('payment_date')->title('Date'),
            Column::make('po_no')->title('PO Ref'),
            Column::make('vendor_name')->title('Vendor'),
            Column::make('payment_method')->title('Method'),
            Column::make('amount')->title('Amount'),
            Column::make('base_amount')->title('Base Amount'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(180)
                  ->addClass('text-center'),
        ];
    }
}
