<?php

namespace App\DataTables;

use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderPaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<OrderPayment> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('order_link', function ($query) {
                return '<a href="' . route('admin.orders.show', $query->order_id) . '"><strong>' . e($query->order->order_no) . '</strong></a>';
            })
            ->addColumn('customer', function ($query) {
                return e($query->order->billing_name);
            })
            ->addColumn('receipts', function ($query) {
                if (!$query->relationLoaded('receipts') || $query->receipts->isEmpty()) {
                    return '<span class="text-muted">N/A</span>';
                }

                $count = $query->receipts->count();
                $items = $query->receipts->map(function ($receipt, $index) {
                    $label = $receipt->original_name ?: ('Receipt ' . ($index + 1));
                    $download = '<a class="dropdown-item" href="' .
                        route('admin.accounts.receipts.download', $receipt->id) .
                        '"><i class="fas fa-download mr-2"></i>Download</a>';
                    $delete = '<a class="dropdown-item text-danger delete-item" href="' .
                        route('admin.accounts.receipts.destroy', $receipt->id) .
                        '"><i class="fas fa-trash mr-2"></i>Delete</a>';
                    return '<div class="px-3 py-2 border-bottom">' .
                        '<div class="text-dark font-weight-bold small mb-1" title="' . e($label) . '">' . e($label) . '</div>' .
                        '<div class="d-flex align-items-center gap-2">' .
                        '<a class="btn btn-sm btn-light border mr-2" href="' .
                        route('admin.accounts.receipts.download', $receipt->id) .
                        '"><i class="fas fa-download mr-1"></i>Download</a>' .
                        '<a class="btn btn-sm btn-outline-danger delete-item" href="' .
                        route('admin.accounts.receipts.destroy', $receipt->id) .
                        '"><i class="fas fa-trash mr-1"></i>Delete</a>' .
                        '</div>' .
                        '</div>';
                })->implode('');

                return '<div class="dropdown d-inline-block">' .
                    '<button class="btn btn-sm btn-outline-secondary dropdown-toggle px-2 py-1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
                    '<i class="fas fa-paperclip mr-1"></i>' .
                    'Receipts <span class="badge badge-secondary ml-1">' . $count . '</span>' .
                    '</button>' .
                    '<div class="dropdown-menu dropdown-menu-right shadow-sm p-0" style="min-width: 260px;">' .
                    '<div class="px-3 py-2 border-bottom bg-light text-muted small text-uppercase">Attachments</div>' .
                    $items .
                    '</div>' .
                    '</div>';
            })
            ->addColumn('payment_pdf', function ($query) {
                $pdf = '<a class="btn btn-sm btn-warning mr-1" href="' .
                    route('admin.accounts.payments.single.pdf', $query->id) .
                    '"><i class="fas fa-file-pdf mr-1"></i></a>';
                $view = '<a class="btn btn-sm btn-outline-info" href="' .
                    route('admin.accounts.payments.single.view', $query->id) .
                    '" target="_blank"><i class="fas fa-eye mr-1"></i></a>';
                return $pdf . $view;
            })
            ->filterColumn('order_link', function ($query, $keyword) {
                $query->whereHas('order', function ($q) use ($keyword) {
                    $q->where('order_no', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('customer', function ($query, $keyword) {
                $query->whereHas('order', function ($q) use ($keyword) {
                    $q->where('billing_name', 'like', '%' . $keyword . '%')
                        ->orWhere('billing_phone', 'like', '%' . $keyword . '%');
                });
            })
            ->filter(function ($query) {
                $keyword = request('search.value');
                if (!$keyword) {
                    return;
                }

                $query->where(function ($q) use ($keyword) {
                    $q->where('transaction_id', 'like', '%' . $keyword . '%')
                        ->orWhere('payment_method', 'like', '%' . $keyword . '%')
                        ->orWhereHas('order', function ($oq) use ($keyword) {
                            $oq->where('order_no', 'like', '%' . $keyword . '%')
                                ->orWhere('billing_name', 'like', '%' . $keyword . '%')
                                ->orWhere('billing_phone', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->editColumn('payment_method', function ($query) {
                return '<span class="badge badge-info">' . strtoupper(e($query->payment_method)) . '</span>';
            })
            ->editColumn('amount', function ($query) {
                return number_format($query->amount, 2);
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at->format('d M, Y h:i A');
            })
            ->rawColumns(['order_link', 'payment_method', 'receipts', 'payment_pdf'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<OrderPayment>
     */
    public function query(OrderPayment $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['order', 'receipts']);

        if (request()->filled('start_date')) {
            $query->whereDate('created_at', '>=', request()->start_date);
        }

        if (request()->filled('end_date')) {
            $query->whereDate('created_at', '<=', request()->end_date);
        }

        if (request()->filled('method')) {
            $query->where('payment_method', request()->method);
        }

        return $query->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('order-payment-table')
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
            Column::computed('order_link')->title('Order No'),
            Column::computed('customer')->title('Customer'),
            Column::computed('payment_method')->title('Method'),
            Column::make('transaction_id')->title('Trans ID'),
            Column::computed('receipts')->title('Receipts')->orderable(false)->searchable(false),
            Column::computed('payment_pdf')->title('PDF')->orderable(false)->searchable(false),
            Column::make('amount')->title('Amount'),
            Column::make('note')->title('Note'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Payments_' . date('YmdHis');
    }
}
