<?php

namespace App\DataTables;

use App\Models\VendorBill;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VendorBillDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                $viewBtn = '<a href="' . route('admin.vendor-bills.show', $row->id) . '" class="btn btn-sm btn-info mr-1" title="View Bill Details"><i class="fas fa-eye"></i> View</a>';
                
                $payBtn = '';
                if (in_array($row->payment_status, ['unpaid', 'partial'])) {
                    $payBtn = '<a href="' . route('admin.purchase-payments.create', ['bill_id' => $row->id]) . '" class="btn btn-sm btn-success mr-1" title="Record Payment"><i class="fas fa-money-bill-wave"></i> Pay</a>';
                }

                return $viewBtn . $payBtn;
            })
            ->editColumn('bill_date', function ($row) {
                return $row->bill_date ? $row->bill_date->format('d M Y') : 'N/A';
            })
            ->editColumn('due_date', function ($row) {
                return $row->due_date ? $row->due_date->format('d M Y') : 'N/A';
            })
            ->editColumn('grand_total', function ($row) {
                $symbol = $row->currency ? $row->currency->symbol : (getSettings()->currency_icon ?? 'Kr.');
                return $symbol . ' ' . number_format($row->grand_total, 2);
            })
            ->editColumn('paid_amount', function ($row) {
                $symbol = $row->currency ? $row->currency->symbol : (getSettings()->currency_icon ?? 'Kr.');
                return $symbol . ' ' . number_format($row->paid_amount, 2);
            })
            ->editColumn('due_amount', function ($row) {
                $symbol = $row->currency ? $row->currency->symbol : (getSettings()->currency_icon ?? 'Kr.');
                return $symbol . ' ' . number_format($row->due_amount, 2);
            })
            ->addColumn('vendor_name', function ($row) {
                return $row->vendor ? '<strong>' . e($row->vendor->name) . '</strong>' : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('po_no', function ($row) {
                return $row->purchase ? '<span class="badge badge-light border font-monospace">' . e($row->purchase->po_no) . '</span>' : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('payment_status_badge', function ($row) {
                return $row->formatted_status;
            })
            ->addColumn('match_status', function ($row) {
                if ($row->goods_receipt_id || $row->goodsReceipt) {
                    return '<span class="badge badge-success px-2 py-1 font-weight-bold" title="PO, Goods Receipt & Bill 3-Way Matched"><i class="fas fa-check-double mr-1"></i> 3-Way Matched</span>';
                }
                return '<span class="badge badge-info px-2 py-1 font-weight-bold" title="Direct Purchase Order Invoicing"><i class="fas fa-file-invoice mr-1"></i> PO Invoiced</span>';
            })
            ->rawColumns(['action', 'vendor_name', 'po_no', 'payment_status_badge', 'match_status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(VendorBill $model): QueryBuilder
    {
        return $model->newQuery()->with(['purchase', 'vendor', 'goodsReceipt']);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('vendorbill-table')
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
            Column::make('id')->title('ID')->visible(false),
            Column::make('bill_no')->title('Bill No'),
            Column::make('po_no')->title('PO Ref'),
            Column::make('vendor_name')->title('Vendor'),
            Column::make('bill_date')->title('Bill Date'),
            Column::make('due_date')->title('Due Date'),
            Column::make('grand_total')->title('Total Amount'),
            Column::make('paid_amount')->title('Paid'),
            Column::make('due_amount')->title('Due'),
            Column::make('payment_status_badge')->title('Status'),
            Column::computed('match_status')->title('3-Way Match')->width(130)->addClass('text-center'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(180)
                  ->addClass('text-center'),
        ];
    }
}
