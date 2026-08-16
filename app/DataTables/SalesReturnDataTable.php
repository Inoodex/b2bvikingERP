<?php

namespace App\DataTables;

use App\Models\SalesReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesReturnDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('return_no', function ($query) {
                return '<a href="' . route('admin.sales-returns.show', $query->id) . '" class="font-weight-bold">#' . e($query->return_no) . '</a>';
            })
            ->addColumn('order_no', function ($query) {
                return $query->order ? '<a href="' . route('admin.orders.show', $query->order_id) . '">#' . e($query->order->order_no) . '</a>' : '-';
            })
            ->addColumn('customer', function ($query) {
                return $query->order && $query->order->user ? e($query->order->user->outlet_name ?: $query->order->user->name) : 'Guest';
            })
            ->addColumn('refund_amount', function ($query) {
                return 'kr. ' . number_format((float)$query->refund_amount, 2);
            })
            ->addColumn('return_to_stock', function ($query) {
                if ($query->status !== 'approved') {
                    return '<span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> Pending</span>';
                }

                $scrapCount = $query->items->whereIn('disposition', ['scrap', 'rtv'])->count();
                $restockCount = $query->items->where('disposition', 'restock')->count();
                $totalItems = $query->items->count();

                if ($totalItems > 0 && $scrapCount === $totalItems) {
                    return '<span class="badge badge-danger"><i class="fas fa-trash-alt mr-1"></i> Scrapped / Damaged</span>';
                } elseif ($scrapCount > 0 && $restockCount > 0) {
                    return '<span class="badge badge-warning text-dark"><i class="fas fa-adjust mr-1"></i> Partial Restock</span>';
                }

                return '<span class="badge badge-info"><i class="fas fa-boxes mr-1"></i> Restocked</span>';
            })
            ->addColumn('status', function ($query) {
                if ($query->status === 'approved') {
                    return '<span class="badge badge-success">Approved</span>';
                } elseif ($query->status === 'rejected' || $query->status === 'cancelled') {
                    return '<span class="badge badge-danger">Rejected</span>';
                }
                return '<span class="badge badge-warning">Pending</span>';
            })
            ->addColumn('action', function ($query) {
                $viewBtn = '<a href="' . route('admin.sales-returns.show', $query->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>';
                $cnBtn = '';
                if ($query->creditNote) {
                    $cnBtn = '<a href="' . route('admin.credit-notes.show', $query->creditNote->id) . '" class="btn btn-warning btn-sm ml-1" title="Credit Note"><i class="fas fa-file-invoice-dollar"></i></a>';
                }
                return $viewBtn . $cnBtn;
            })
            ->rawColumns(['action', 'return_no', 'order_no', 'return_to_stock', 'status'])
            ->setRowId('id');
    }

    public function query(SalesReturn $model)
    {
        return $model->newQuery()->with(['order.user', 'creditNote', 'items'])->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sales-return-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('return_no')->title('Return No')->addClass('text-center'),
            Column::computed('order_no')->title('Order No')->addClass('text-center'),
            Column::computed('customer')->title('Customer')->addClass('text-center'),
            Column::make('refund_amount')->title('Amount')->addClass('text-center'),
            Column::make('return_to_stock')->title('Restock')->addClass('text-center'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::computed('action')->title('Action')->addClass('text-center')
                ->exportable(false)
                ->printable(false)
                ->width(100),
        ];
    }

    protected function filename(): string
    {
        return 'SalesReturn_' . date('YmdHis');
    }
}
