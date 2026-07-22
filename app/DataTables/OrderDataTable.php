<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
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
            ->addColumn('outlet_shop', function ($query) {
                $name = $query->billing_outlet_name ?: ($query->user->outlet_name ?? null);
                return e($name ?: 'N/A');
            })
            ->addColumn('items_count', function ($query) {
                return '<span class="badge badge-info">' . (int) $query->items_count . ' Items</span>';
            })
            ->addColumn('total_amount_label', function ($query) {
                // If this order has issues, compute displayed total from issued items
                try {
                    $hasIssues = \App\Models\Issue::where('order_id', $query->id)->exists();
                    if ($hasIssues) {
                        $issuedTotal = 0;
                        $issueItems = \App\Models\IssueItem::whereHas('issue', function($q) use ($query) {
                            $q->where('order_id', $query->id);
                        })->get();

                        $orderItems = ($query->relationLoaded('items') ? $query->items : $query->items()->get())
                            ->keyBy(function($it) {
                                return $it->product_id . '_' . ($it->variant_id ?? 0);
                            });

                        foreach ($issueItems as $ii) {
                            $key = $ii->product_id . '_' . ($ii->variant_id ?? 0);
                            if (isset($orderItems[$key])) {
                                $issuedTotal += ($orderItems[$key]->unit_price ?? 0) * $ii->quantity;
                            }
                        }

                        return number_format((float) $issuedTotal, 2);
                    }
                } catch (\Throwable $e) {
                    // Fallback to stored total on error
                }

                return number_format((float) $query->total_amount, 2);
            })
            ->editColumn('created_at', function ($query) {
                return optional($query->created_at)->format('d M, Y h:i A');
            })
            ->addColumn('status_badge', function ($query) {
                $status = strtolower((string) $query->status);
                $class = 'badge-secondary';

                if ($status === 'pending') {
                    $class = 'badge-warning';
                } elseif ($status === 'approved') {
                    $class = 'badge-info';
                } elseif ($status === 'processing') {
                    $class = 'badge-primary';
                } elseif ($status === 'shipped') {
                    $class = 'badge-primary';
                } elseif ($status === 'completed') {
                    $class = 'badge-success';
                } elseif ($status === 'rejected') {
                    $class = 'badge-danger';
                } elseif ($status === 'cancelled') {
                    $class = 'badge-danger';
                }

                return '<span class="badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
            })
            ->addColumn('action', function ($query) {
                $view = "<a href='" . route('admin.orders.show', $query->id) . "' class='btn btn-primary btn-sm mr-1' title='Control Panel'><i class='fas fa-eye'></i></a>";
                $pi_invoice = "<a href='" . route('admin.orders.pi-invoice', $query->id) . "' target='_blank' class='btn btn-success btn-sm mr-1' title='PI Invoice'>PI</a>";
                $invoice = "<a href='" . route('admin.orders.view-invoice', $query->id) . "' target='_blank' class='btn btn-warning btn-sm mr-1' title='View Invoice'><i class='fas fa-file-invoice'></i></a>";
                $download = "<a href='" . route('admin.orders.download-invoice', $query->id) . "' class='btn btn-info btn-sm mr-1' title='Download PDF'><i class='fas fa-download'></i></a>";
                $delete = "<a href='" . route('admin.orders.destroy', $query->id) . "' class='btn btn-danger btn-sm delete-item' title='Delete'><i class='fas fa-trash'></i></a>";
                $issue = '';
                $canCreateIssue = Auth::check() && Auth::user()->hasRole('Admin');
                if ($canCreateIssue && strtolower((string) $query->status) === 'approved') {
                    $issue = "<a href='" . route('admin.issues.create', ['order_id' => $query->id]) . "' class='btn btn-success btn-sm' title='Create Stock Issue'><i class='fas fa-box-open'></i></a>";
                }

                $pay = '';
                if (Auth::user()->hasRole('Admin') && (float)$query->due_amount > 0 && $query->status === 'completed') {
                    $pay = "<a href='" . route('admin.accounts.record-payment', ['order_no' => $query->order_no]) . "' class='btn btn-dark btn-sm mr-1' title='Record Payment'><i class='fas fa-money-bill-wave'></i></a>";
                }

                return $pay . $pi_invoice . $invoice . $download . $view . $issue . ' ' . $delete;
            })
            ->rawColumns(['customer', 'items_count', 'status_badge', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Order>
     */
    public function query(Order $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with('user')
            ->with('items')
            ->withCount('items');

        if (request()->filled('status')) {
            $query->where('status', request()->status);
        }

        if (request()->filled('user_id')) {
            $query->where('user_id', request()->user_id);
        }

        return $query->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('order-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) { d.status = $("#filter_status").val(); d.user_id = $("#filter_user").val(); }'
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
            // Column::make('id'),
            Column::make('order_no')->title('Order No'),
            Column::computed('customer')->title('Customer'),
            Column::computed('outlet_shop')->title('Outlet/Shop'),
            Column::computed('items_count')->title('Items'),
            Column::computed('total_amount_label')->title('Total'),
            Column::make('shipping_method')->title('Source'),
            Column::computed('status_badge')->title('Status'),
            Column::make('created_at')->title('Placed At'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(200)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Orders_' . date('YmdHis');
    }
}
