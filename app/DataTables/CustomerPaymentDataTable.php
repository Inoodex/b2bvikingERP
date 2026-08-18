<?php

namespace App\DataTables;

use App\Models\CustomerPayment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerPaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('customer_name', function ($row) {
                return $row->user ? ($row->user->outlet_name ?: $row->user->name) : 'Guest / Cash';
            })
            ->addColumn('invoice_no', function ($row) {
                if ($row->invoice) {
                    return '<a href="' . route('admin.sales-invoices.show', $row->sales_invoice_id) . '" class="font-weight-bold text-primary">' . e($row->invoice->invoice_no) . '</a>';
                }
                return '<span class="badge badge-light text-muted">N/A (Deposit/Order)</span>';
            })
            ->editColumn('amount', function ($row) {
                return '<strong class="text-success">kr. ' . number_format((float)$row->amount, 2) . '</strong>';
            })
            ->editColumn('payment_method', function ($row) {
                $methodNames = [
                    'cash' => '<span class="badge badge-success px-2 py-1"><i class="fas fa-money-bill-wave mr-1"></i> Cash</span>',
                    'bank_transfer' => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-university mr-1"></i> Bank Transfer</span>',
                    'cheque' => '<span class="badge badge-info px-2 py-1"><i class="fas fa-money-check mr-1"></i> Cheque</span>',
                    'card' => '<span class="badge badge-warning px-2 py-1"><i class="fas fa-credit-card mr-1"></i> Card</span>',
                    'mobile_money' => '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-mobile-alt mr-1"></i> Mobile Money</span>',
                ];
                return $methodNames[$row->payment_method] ?? '<span class="badge badge-dark">' . e($row->payment_method) . '</span>';
            })
            ->editColumn('payment_date', function ($row) {
                return $row->payment_date ? $row->payment_date->format('d M, Y') : '-';
            })
            ->editColumn('status', function ($row) {
                return '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Posted</span>';
            })
            ->addColumn('action', function ($row) {
                $showBtn = '<a href="' . route('admin.customer-payments.show', $row->id) . '" class="btn btn-sm btn-info mr-1" title="View Receipt"><i class="fas fa-eye"></i></a>';
                $pdfBtn = '<a href="' . route('admin.customer-payments.pdf', $row->id) . '" target="_blank" class="btn btn-sm btn-danger mr-1" title="Download Receipt PDF"><i class="fas fa-file-pdf"></i></a>';

                return '<div class="btn-group">' . $showBtn . $pdfBtn . '</div>';
            })
            ->rawColumns(['customer_name', 'invoice_no', 'amount', 'payment_method', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CustomerPayment $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['user', 'invoice', 'order', 'account'])
            ->latest('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('customerpayment-table')
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
            Column::make('id')->title('ID')->width(50),
            Column::make('payment_no')->title('Receipt No')->addClass('font-weight-bold'),
            Column::computed('customer_name')->title('Customer'),
            Column::computed('invoice_no')->title('Sales Invoice'),
            Column::make('payment_method')->title('Payment Method'),
            Column::make('amount')->title('Amount Received'),
            Column::make('payment_date')->title('Payment Date'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(100)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'CustomerPayment_' . date('YmdHis');
    }
}
