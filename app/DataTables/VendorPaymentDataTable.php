<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\PurchasePayment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VendorPaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<PurchasePayment> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('invoice_link', function ($payment) {
                if ($payment->purchase) {
                    return '<a href="' . route('admin.purchases.show', $payment->purchase->id) . '" class="font-weight-bold text-primary font-monospace">' . e($payment->purchase->invoice_no ?? 'PO-' . $payment->purchase->id) . '</a>';
                }
                return '<span class="text-muted font-monospace small">' . e($payment->payment_no ?? '—') . '</span>';
            })
            ->addColumn('vendor_name', function ($payment) {
                $vendor = $payment->purchase?->vendor ?? $payment->vendor;
                if (!$vendor) {
                    return '<span class="text-muted">N/A</span>';
                }
                $html = '<strong>' . e($vendor->shop_name ?? $vendor->name) . '</strong>';
                if (!empty($vendor->phone)) {
                    $html .= '<br><small class="text-muted"><i class="fas fa-phone mr-1"></i>' . e($vendor->phone) . '</small>';
                }
                return $html;
            })
            ->addColumn('receipts', function ($payment) {
                if (!$payment->relationLoaded('receipts') || $payment->receipts->isEmpty()) {
                    return '<span class="text-muted small">—</span>';
                }

                $count = $payment->receipts->count();
                $items = $payment->receipts->map(function ($receipt, $index) {
                    $label = $receipt->original_name ?: ('Receipt ' . ($index + 1));
                    return '<div class="px-3 py-2 border-bottom">' .
                        '<div class="text-dark font-weight-bold small mb-1" title="' . e($label) . '">' . e($label) . '</div>' .
                        '<div class="d-flex align-items-center">' .
                        '<a class="btn btn-sm btn-light border mr-2" href="' .
                        route('admin.accounts.vendor-payments.receipts.download', $receipt->id) .
                        '"><i class="fas fa-download mr-1"></i>Download</a>' .
                        '<a class="btn btn-sm btn-outline-danger delete-item" href="' .
                        route('admin.accounts.vendor-payments.receipts.destroy', $receipt->id) .
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
                    '<div class="px-3 py-2 border-bottom bg-light text-muted small text-uppercase font-weight-bold">Attachments</div>' .
                    $items .
                    '</div>' .
                    '</div>';
            })
            ->addColumn('payment_pdf', function ($payment) {
                $pdf = '<a class="btn btn-sm btn-warning mr-1" href="' .
                    route('admin.accounts.vendor-payments.single.pdf', $payment->id) .
                    '" title="Download PDF Voucher"><i class="fas fa-file-pdf"></i></a>';
                $view = '<a class="btn btn-sm btn-outline-info" href="' .
                    route('admin.accounts.vendor-payments.single.view', $payment->id) .
                    '" target="_blank" title="View Voucher"><i class="fas fa-eye"></i></a>';
                return $pdf . $view;
            })
            ->editColumn('payment_method', function ($payment) {
                $method = (string) $payment->payment_method;
                $color = match (strtolower($method)) {
                    'cash'           => 'success',
                    'bank'           => 'primary',
                    'mobile_banking' => 'info',
                    'cheque'         => 'warning',
                    default          => 'secondary',
                };
                return '<span class="badge badge-' . $color . ' font-weight-bold">' . strtoupper(e(str_replace('_', ' ', $method))) . '</span>';
            })
            ->editColumn('amount', function ($payment) {
                $formatted = function_exists('formatConverted') ? formatConverted($payment->amount) : ('kr. ' . number_format((float) $payment->amount, 2));
                return '<span class="font-weight-bold text-success">' . $formatted . '</span>';
            })
            ->editColumn('created_at', function ($payment) {
                $date = $payment->payment_date ? Carbon::parse($payment->payment_date) : $payment->created_at;
                return '<small>' . $date->format('d M, Y') . '<br><span class="text-muted">' . $date->format('h:i A') . '</span></small>';
            })
            ->filterColumn('invoice_link', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('payment_no', 'like', '%' . $keyword . '%')
                        ->orWhereHas('purchase', function ($pq) use ($keyword) {
                            $pq->where('invoice_no', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->filterColumn('vendor_name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('purchase.vendor', function ($vq) use ($keyword) {
                        $vq->where('shop_name', 'like', '%' . $keyword . '%')
                            ->orWhere('name', 'like', '%' . $keyword . '%')
                            ->orWhere('phone', 'like', '%' . $keyword . '%');
                    })->orWhereHas('vendor', function ($vq) use ($keyword) {
                        $vq->where('shop_name', 'like', '%' . $keyword . '%')
                            ->orWhere('name', 'like', '%' . $keyword . '%')
                            ->orWhere('phone', 'like', '%' . $keyword . '%');
                    });
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
                        ->orWhere('note', 'like', '%' . $keyword . '%')
                        ->orWhereHas('purchase', function ($pq) use ($keyword) {
                            $pq->where('invoice_no', 'like', '%' . $keyword . '%')
                                ->orWhereHas('vendor', function ($vq) use ($keyword) {
                                    $vq->where('shop_name', 'like', '%' . $keyword . '%')
                                        ->orWhere('phone', 'like', '%' . $keyword . '%');
                                });
                        });
                });
            })
            ->rawColumns(['invoice_link', 'vendor_name', 'payment_method', 'receipts', 'payment_pdf', 'amount', 'created_at'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(PurchasePayment $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['purchase.vendor', 'vendor', 'receipts']);

        if (request()->filled('vendor_id')) {
            $vendorId = (int) request()->vendor_id;
            $query->where(function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                    ->orWhereHas('purchase', function ($pq) use ($vendorId) {
                        $pq->where('vendor_id', $vendorId);
                    });
            });
        }

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
            ->setTableId('vendor-payment-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->width(50)->visible(false),
            Column::computed('created_at')->title('Date'),
            Column::computed('invoice_link')->title('Invoice / PO No'),
            Column::computed('vendor_name')->title('Vendor / Supplier'),
            Column::computed('payment_method')->title('Method'),
            Column::make('transaction_id')->title('Trans ID'),
            Column::computed('receipts')->title('Receipts')->orderable(false)->searchable(false),
            Column::computed('payment_pdf')->title('PDF')->orderable(false)->searchable(false),
            Column::computed('amount')->title('Amount')->addClass('text-right'),
            Column::make('note')->title('Note'),
        ];
    }

    protected function filename(): string
    {
        return 'VendorPayments_' . date('YmdHis');
    }
}
