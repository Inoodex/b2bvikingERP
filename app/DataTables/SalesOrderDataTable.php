<?php

namespace App\DataTables;

use App\Models\Order;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesOrderDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $view = "<a href='" . route('admin.orders.show', $query->id) . "' class='btn btn-primary btn-sm mr-1' title='Control Panel'><i class='fas fa-eye'></i></a>";
                $pi_invoice = "<a href='" . route('admin.orders.pi-invoice', $query->id) . "' target='_blank' class='btn btn-success btn-sm mr-1' title='PI Invoice'>PI</a>";
                $invoice = "<a href='" . route('admin.orders.view-invoice', $query->id) . "' target='_blank' class='btn btn-warning btn-sm mr-1' title='View Invoice'><i class='fas fa-file-invoice'></i></a>";
                $download = "<a href='" . route('admin.orders.download-invoice', $query->id) . "' class='btn btn-info btn-sm mr-1' title='Download PDF'><i class='fas fa-download'></i></a>";
                $delete = "<a href='" . route('admin.orders.destroy', $query->id) . "' class='btn btn-danger btn-sm delete-item mr-1' title='Delete'><i class='fas fa-trash'></i></a>";
                
                $deliveryOrder = '';
                $canManage = \Illuminate\Support\Facades\Auth::check() && (\Illuminate\Support\Facades\Auth::user()->hasRole('Admin') || \Illuminate\Support\Facades\Auth::user()->can('Manage Inventory'));
                if ($canManage && in_array(strtolower((string) $query->status), ['approved', 'processing', 'completed'])) {
                    $deliveryOrder = "<a href='" . route('admin.delivery-orders.create', ['order_id' => $query->id]) . "' class='btn btn-outline-primary btn-sm mr-1' title='Create Delivery Challan'><i class='fas fa-truck'></i></a>";
                }

                $pay = '';
                if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->hasRole('Admin') && (float)$query->due_amount > 0 && strtolower((string)$query->status) === 'completed') {
                    $pay = "<a href='" . route('admin.accounts.record-payment', ['order_no' => $query->order_no]) . "' class='btn btn-dark btn-sm mr-1' title='Record Payment'><i class='fas fa-money-bill-wave'></i></a>";
                }

                return $pay . $pi_invoice . $invoice . $download . $deliveryOrder . $view . ' ' . $delete;
            })
            ->addColumn('order_no_badge', function ($query) {
                return '<span class="badge badge-dark px-3 py-1 font-weight-bold" style="font-family: monospace; font-size: 0.9rem; letter-spacing: 1px;"><i class="fas fa-file-invoice text-warning mr-1"></i>' . e($query->order_no) . '</span>';
            })
            ->addColumn('customer_info', function ($query) {
                $name = $query->user?->name ?? $query->billing_name ?? 'Guest Customer';
                $email = $query->user?->email ?? $query->billing_email ?? '';
                return '<div><strong>' . e($name) . '</strong><br><small class="text-muted">' . e($email) . '</small></div>';
            })
            ->addColumn('order_date', function ($query) {
                $date = $query->placed_at ?? $query->created_at;
                $formatted = $date ? (is_string($date) ? date('d M, Y H:i', strtotime($date)) : $date->format('d M, Y H:i')) : 'N/A';
                return '<small class="font-weight-bold text-dark">' . $formatted . '</small>';
            })
            ->addColumn('financial_total', function ($query) {
                return '<strong class="text-primary" style="font-size: 1rem;">kr. ' . number_format($query->total_amount, 2) . '</strong>';
            })
            ->addColumn('credit_status_badge', function ($query) {
                if ($query->status === 'credit_hold') {
                    return '<span class="badge badge-danger px-3 py-1 font-weight-bold"><i class="fas fa-lock mr-1"></i> CREDIT HOLD</span>';
                }
                return '<span class="badge badge-success px-3 py-1 font-weight-bold"><i class="fas fa-check-circle mr-1"></i> APPROVED</span>';
            })
            ->addColumn('order_status_badge', function ($query) {
                $statusMap = [
                    'draft' => 'badge-secondary',
                    'credit_hold' => 'badge-danger',
                    'pending_approval' => 'badge-warning',
                    'approved' => 'badge-info',
                    'processing' => 'badge-primary',
                    'delivered' => 'badge-success',
                    'cancelled' => 'badge-dark',
                ];
                $class = $statusMap[$query->status] ?? 'badge-secondary';
                return '<span class="badge ' . $class . ' px-3 py-1 font-weight-bold text-uppercase">' . str_replace('_', ' ', $query->status) . '</span>';
            })
            ->addColumn('payment_status_badge', function ($query) {
                $class = 'badge-secondary';
                if ($query->payment_status === 'paid') {
                    $class = 'badge-success';
                } elseif ($query->payment_status === 'partial') {
                    $class = 'badge-warning';
                } elseif ($query->payment_status === 'unpaid') {
                    $class = 'badge-danger';
                }
                return '<span class="badge ' . $class . ' px-3 py-1 font-weight-bold text-uppercase">' . e($query->payment_status) . '</span>';
            })
            ->rawColumns(['action', 'order_no_badge', 'customer_info', 'order_date', 'financial_total', 'credit_status_badge', 'order_status_badge', 'payment_status_badge'])
            ->setRowId('id');
    }

    public function query(Order $model)
    {
        $query = $model->newQuery()
            ->with(['user'])
            ->where(function ($q) {
                $q->where('order_no', 'like', 'SO-%')
                  ->orWhere('order_no', 'like', 'DS-ORD-%')
                  ->orWhere('order_no', 'like', 'DS-REQ-%')
                  ->orWhere('shipping_method', 'admin_request')
                  ->orWhereNotNull('quotation_id');
            })
            ->latest();

        $status = $this->request()->get('status') ?: $this->request()->get('status_filter');
        if (!empty($status)) {
            $query->where('status', $status);
        }

        if ($this->request()->filled('user_id')) {
            $query->where('user_id', $this->request()->get('user_id'));
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sales-order-table')
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
            Column::make('order_no_badge')->title('Sales Order #'),
            Column::make('customer_info')->title('Customer'),
            Column::make('order_date')->title('Order Date'),
            Column::make('financial_total')->title('Total Amount'),
            Column::make('credit_status_badge')->title('Credit Line'),
            Column::make('order_status_badge')->title('Order Status'),
            Column::make('payment_status_badge')->title('Payment'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Sales_Orders_' . date('YmdHis');
    }
}
