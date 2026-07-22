<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DueOrderDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Order> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('customer', function ($query) {
                return '<strong>' . e($query->billing_name) . '</strong><br><small>' . e($query->billing_phone) . '</small>';
            })
            ->editColumn('total_amount', function ($query) {
                return number_format($query->total_amount, 2);
            })
            ->editColumn('paid_amount', function ($query) {
                return '<span class="text-success">' . number_format($query->paid_amount, 2) . '</span>';
            })
            ->editColumn('due_amount', function ($query) {
                return '<span class="text-danger font-weight-bold">' . number_format($query->due_amount, 2) . '</span>';
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at->format('d M, Y');
            })
            ->addColumn('action', function ($query) {
                if ($query->status === 'completed') {
                    return '<a href="' . route('admin.accounts.record-payment', ['order_no' => $query->order_no]) . '" class="btn btn-dark btn-sm" title="Record Payment"><i class="fas fa-money-bill-wave"></i> Pay Now</a>';
                }
                return '<span class="text-danger small">create issue</span>';
            })
            ->rawColumns(['customer', 'paid_amount', 'due_amount', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Order>
     */
    public function query(Order $model): QueryBuilder
    {
        $query = $model->newQuery()->where('due_amount', '>', 0);

        if (request()->filled('start_date')) {
            $query->whereDate('created_at', '>=', request()->start_date);
        }

        if (request()->filled('end_date')) {
            $query->whereDate('created_at', '<=', request()->end_date);
        }

        if (request()->filled('customer')) {
            $query->where(function($q) {
                $q->where('billing_name', 'like', '%' . request()->customer . '%')
                  ->orWhere('billing_phone', 'like', '%' . request()->customer . '%')
                  ->orWhere('order_no', 'like', '%' . request()->customer . '%');
            });
        }

        return $query->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('due-order-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
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
            Column::make('id')->width(50),
            Column::make('created_at')->title('Date'),
            Column::make('order_no')->title('Order No'),
            Column::computed('customer')->title('Customer'),
            Column::make('total_amount')->title('Total'),
            Column::make('paid_amount')->title('Paid'),
            Column::make('due_amount')->title('Due'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Due_Orders_' . date('YmdHis');
    }
}
