<?php

namespace App\DataTables;

use App\Models\SalesInvoice;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesInvoiceDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('invoice_no', function ($query) {
                return '<a href="' . route('admin.sales-invoices.show', $query->id) . '" class="font-weight-bold">#' . e($query->invoice_no) . '</a>';
            })
            ->addColumn('order_no', function ($query) {
                return $query->order ? '<a href="' . route('admin.orders.show', $query->order_id) . '">#' . e($query->order->order_no) . '</a>' : '-';
            })
            ->addColumn('customer', function ($query) {
                return $query->order && $query->order->user ? e($query->order->user->outlet_name ?: $query->order->user->name) : 'Guest';
            })
            ->addColumn('total_amount', function ($query) {
                return '<span class="font-weight-bold text-dark">kr. ' . number_format((float)$query->total_amount, 2) . '</span>';
            })
            ->addColumn('paid_amount', function ($query) {
                return '<span class="text-success">kr. ' . number_format((float)$query->paid_amount, 2) . '</span>';
            })
            ->addColumn('due_amount', function ($query) {
                $due = (float)$query->due_amount;
                $color = $due > 0 ? 'text-danger font-weight-bold' : 'text-muted';
                return '<span class="' . $color . '">kr. ' . number_format($due, 2) . '</span>';
            })
            ->addColumn('status', function ($query) {
                if ($query->status === 'posted') {
                    return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Posted</span>';
                } elseif ($query->status === 'paid') {
                    return '<span class="badge badge-info"><i class="fas fa-money-check-alt mr-1"></i> Fully Paid</span>';
                } elseif ($query->status === 'cancelled') {
                    return '<span class="badge badge-danger">Cancelled</span>';
                }
                return '<span class="badge badge-warning"><i class="fas fa-file-signature mr-1"></i> Draft</span>';
            })
            ->addColumn('action', function ($query) {
                $viewBtn = '<a href="' . route('admin.sales-invoices.show', $query->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View</a>';
                $pdfBtn = '<a href="' . route('admin.sales-invoices.pdf', $query->id) . '" target="_blank" class="btn btn-danger btn-sm ml-1" title="PDF Invoice"><i class="fas fa-file-pdf"></i></a>';
                return $viewBtn . $pdfBtn;
            })
            ->rawColumns(['action', 'invoice_no', 'order_no', 'total_amount', 'paid_amount', 'due_amount', 'status'])
            ->setRowId('id');
    }

    public function query(SalesInvoice $model): QueryBuilder
    {
        return $model->newQuery()->with(['order.user'])->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sales-invoice-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('invoice_no')->title('Invoice No')->addClass('text-center'),
            Column::computed('order_no')->title('Order No')->addClass('text-center'),
            Column::computed('customer')->title('Customer / Outlet')->addClass('text-center'),
            Column::computed('total_amount')->title('Total Amount')->addClass('text-center'),
            Column::computed('paid_amount')->title('Paid Amount')->addClass('text-center'),
            Column::computed('due_amount')->title('Due Amount')->addClass('text-center'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::computed('action')->title('Action')->addClass('text-center')
                ->exportable(false)
                ->printable(false)
                ->width(140),
        ];
    }

    protected function filename(): string
    {
        return 'SalesInvoice_' . date('YmdHis');
    }
}
