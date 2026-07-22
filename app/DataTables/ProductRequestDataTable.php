<?php

namespace App\DataTables;

use App\Models\ProductRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Support\Facades\Auth;

class ProductRequestDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<ProductRequest> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('customer', function ($query) {
                return '<strong>' . e($query->user->name) . '</strong><br><small>' . e($query->user->phone) . '</small>';
            })
            ->addColumn('outlet_shop', function ($query) {
                return e($query->user->outlet_name ?? 'N/A');
            })
            ->addColumn('items_count', function ($query) {
                return '<span class="badge badge-info">' . $query->items->count() . ' Items</span>';
            })
            ->addColumn('total_amount_label', function ($query) {
                return number_format((float) $query->total_amount, 2);
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at->format('d M, Y h:i A');
            })
            ->addColumn('source', function ($query) {
                if ($query->order && $query->order->shipping_method) {
                    $method = $query->order->shipping_method;
                    if ($method === 'admin_request' || $method === 'manual_repair') {
                        return 'Admin Request';
                    }
                    return ucfirst(str_replace('_', ' ', $method));
                }
                return 'Outlet Request';
            })

            ->addColumn('status_badge', function ($query) {
                $status = strtolower($query->status);
                $class = match($status) {
                    'pending' => 'badge-warning',
                    'approved' => 'badge-info',
                    'rejected' => 'badge-danger',
                    'shipped' => 'badge-primary',
                    'completed' => 'badge-success',
                    default => 'badge-secondary',
                };
                return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('action', function ($query) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                $pay = '';
                if ($query->order && $query->order->due_amount > 0 && $user->hasRole('Admin')) {
                    $pay = '<a href="' . route('admin.accounts.record-payment', ['order_no' => $query->order->order_no]) . '" class="btn btn-dark btn-sm mr-1" title="Record Payment"><i class="fas fa-money-bill-wave"></i></a>';
                }
                
                $piInvoice = '<a href="' . route('admin.product-requests.pi-invoice', $query->id) . '" class="btn btn-success btn-sm mr-1" title="PI Invoice" target="_blank">PI</a>';
                $viewInvoice = '<a href="' . route('admin.product-requests.view-invoice', $query->id) . '" class="btn btn-warning btn-sm mr-1" title="View Invoice" target="_blank"><i class="fas fa-file-invoice"></i></a>';
                $downloadPdf = '<a href="' . route('admin.product-requests.download-invoice', $query->id) . '" class="btn btn-info btn-sm mr-1" title="Download PDF"><i class="fas fa-download"></i></a>';
                $show = '<a href="' . route('admin.product-requests.show', $query->id) . '" class="btn btn-primary btn-sm mr-1" title="Details"><i class="fas fa-eye"></i></a>';
                
                $delete = '';
                if ($query->status == 'pending' || $user->can('Manage Product Requests')) {
                    $delete = '<a href="' . route('admin.product-requests.destroy', $query->id) . '" class="btn btn-danger btn-sm delete-item" title="Delete"><i class="fas fa-trash"></i></a>';
                }

                return $pay . $piInvoice . $viewInvoice . $downloadPdf . $show . $delete;
            })
            ->rawColumns(['customer', 'items_count', 'status_badge', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<ProductRequest>
     */
    public function query(ProductRequest $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user', 'order', 'items']);

        if (request()->filled('status')) {
            $query->where('status', request()->status);
        }

        return $query->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-request-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->stateSave(true)
            ->responsive(true)
            ->autoWidth(false)
            ->ajax([
                'data' => 'function(d) { d.status = $("#filter_status").val(); }'
            ])
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
            Column::make('id')->visible(false),
            Column::make('request_no')->title('Order No'),
            Column::computed('customer')->title('Customer'),
            Column::computed('outlet_shop')->title('Outlet/Shop'),
            Column::computed('items_count')->title('Items'),
            Column::computed('total_amount_label')->title('Total'),
            Column::computed('source')->title('Source'),
            Column::computed('status_badge')->title('Status'),
            Column::make('created_at')->title('Placed At'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(180)
                ->addClass('text-center'),
        ];
    }




    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Product_Requests_' . date('YmdHis');
    }
}
