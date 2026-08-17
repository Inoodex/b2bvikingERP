<?php

namespace App\DataTables;

use App\Models\DeliveryOrder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DeliveryOrderDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('delivery_no', function ($query) {
                return '<a href="' . route('admin.delivery-orders.show', $query->id) . '" class="font-weight-bold">#' . e($query->delivery_no) . '</a>';
            })
            ->addColumn('order_no', function ($query) {
                return $query->order ? '<a href="' . route('admin.orders.show', $query->order_id) . '">#' . e($query->order->order_no) . '</a>' : '-';
            })
            ->addColumn('customer', function ($query) {
                return $query->order && $query->order->user ? e($query->order->user->outlet_name ?: $query->order->user->name) : 'Guest';
            })
            ->addColumn('total_qty', function ($query) {
                $sum = (float)$query->items->sum('qty_delivered');
                return '<span class="font-weight-bold text-dark">' . number_format($sum, 2) . '</span>';
            })
            ->addColumn('carrier', function ($query) {
                if ($query->carrier_name || $query->awb_number) {
                    $carrier = e($query->carrier_name ?: 'Logistics');
                    $awb = $query->awb_number ? '<br><small class="text-muted"><i class="fas fa-barcode mr-1"></i>' . e($query->awb_number) . '</small>' : '';
                    return '<strong>' . $carrier . '</strong>' . $awb;
                }
                return '<span class="text-muted">Standard Delivery</span>';
            })
            ->addColumn('status', function ($query) {
                if ($query->status === 'dispatched' || $query->status === 'delivered' || $query->status === 'shipped') {
                    return '<span class="badge badge-success"><i class="fas fa-truck mr-1"></i> Dispatched</span>';
                } elseif ($query->status === 'cancelled') {
                    return '<span class="badge badge-danger">Cancelled</span>';
                }
                return '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Pending Dispatch</span>';
            })
            ->addColumn('action', function ($query) {
                $viewBtn = '<a href="' . route('admin.delivery-orders.show', $query->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View</a>';
                $pdfBtn = '<a href="' . route('admin.delivery-orders.pdf', $query->id) . '" target="_blank" class="btn btn-danger btn-sm ml-1" title="PDF Packing Slip"><i class="fas fa-file-pdf"></i></a>';
                return $viewBtn . $pdfBtn;
            })
            ->rawColumns(['action', 'delivery_no', 'order_no', 'total_qty', 'carrier', 'status'])
            ->setRowId('id');
    }

    public function query(DeliveryOrder $model): QueryBuilder
    {
        return $model->newQuery()->with(['order.user', 'items'])->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('delivery-order-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc');
    }

    public function getColumns(): array
    {
        return [
            Column::make('delivery_no')->title('Delivery No')->addClass('text-center'),
            Column::computed('order_no')->title('Order No')->addClass('text-center'),
            Column::computed('customer')->title('Customer')->addClass('text-center'),
            Column::computed('total_qty')->title('Total Qty')->addClass('text-center'),
            Column::computed('carrier')->title('Carrier & AWB')->addClass('text-center'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::computed('action')->title('Action')->addClass('text-center')
                ->exportable(false)
                ->printable(false)
                ->width(140),
        ];
    }

    protected function filename(): string
    {
        return 'DeliveryOrder_' . date('YmdHis');
    }
}
