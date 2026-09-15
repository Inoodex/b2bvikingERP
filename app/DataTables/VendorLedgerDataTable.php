<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\PurchasePayment;
use App\Models\Vendor;
use App\Models\VendorBill;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VendorLedgerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Vendor> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('code', function ($vendor) {
                return '<span class="badge badge-dark font-monospace">' . e($vendor->code) . '</span>';
            })
            ->addColumn('shop_name', function ($vendor) {
                $name = '<strong>' . e($vendor->shop_name ?? $vendor->name) . '</strong>';
                if (!empty($vendor->address)) {
                    $name .= '<br><small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>' . e($vendor->address) . '</small>';
                }
                return $name;
            })
            ->addColumn('contact', function ($vendor) {
                $contact = '';
                if (!empty($vendor->phone)) {
                    $contact .= '<div><i class="fas fa-phone mr-1 text-muted"></i>' . e($vendor->phone) . '</div>';
                }
                if (!empty($vendor->email)) {
                    $contact .= '<div><small class="text-muted"><i class="fas fa-envelope mr-1"></i>' . e($vendor->email) . '</small></div>';
                }
                return $contact ?: '<span class="text-muted">—</span>';
            })
            ->addColumn('total_billed', function ($vendor) {
                $amount = (float) ($vendor->total_billed ?? 0);
                return 'kr. ' . number_format($amount, 2);
            })
            ->addColumn('total_paid', function ($vendor) {
                $amount = (float) ($vendor->total_paid ?? 0);
                return '<span class="text-success font-weight-bold">kr. ' . number_format($amount, 2) . '</span>';
            })
            ->addColumn('total_due', function ($vendor) {
                $due = (float) ($vendor->total_due ?? 0);
                if ($due > 0) {
                    return '<span class="text-danger font-weight-bold h6 mb-0">kr. ' . number_format($due, 2) . '</span>';
                }
                return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Settled</span>';
            })
            ->addColumn('action', function ($vendor) {
                return '<a href="' . route('admin.vendor-ledger.show', $vendor->id) . '" class="btn btn-sm btn-info font-weight-bold shadow-sm" title="View Statement of Account">
                    <i class="fas fa-file-invoice mr-1"></i> Statement
                </a>';
            })
            ->filter(function ($query) {
                $keyword = request('search.value');
                if ($keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('shop_name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('address', 'like', "%{$keyword}%");
                    });
                }
            })
            ->orderColumn('code', 'vendors.id $1')
            ->orderColumn('total_billed', 'total_billed $1')
            ->orderColumn('total_paid', 'total_paid $1')
            ->orderColumn('total_due', 'total_due $1')
            ->rawColumns(['code', 'shop_name', 'contact', 'total_billed', 'total_paid', 'total_due', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Vendor $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->where('status', 1)
            ->addSelect([
                'vendors.*',
                'total_due' => VendorBill::selectRaw('coalesce(sum(due_amount), 0)')
                    ->whereColumn('vendor_bills.vendor_id', 'vendors.id')
                    ->whereIn('payment_status', ['unpaid', 'partial']),
                'total_billed' => VendorBill::selectRaw('coalesce(sum(grand_total), 0)')
                    ->whereColumn('vendor_bills.vendor_id', 'vendors.id'),
                'total_paid' => PurchasePayment::selectRaw('coalesce(sum(amount), 0)')
                    ->whereColumn('purchase_payments.vendor_id', 'vendors.id'),
            ]);

        $balanceFilter = request()->get('balance_filter');
        if ($balanceFilter === 'has_due') {
            $query->having('total_due', '>', 0);
        } elseif ($balanceFilter === 'settled') {
            $query->having('total_due', '<=', 0);
        }

        return $query->orderByDesc('total_due');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('vendor-ledger-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(5, 'desc')
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('code')->title('Code')->width(90)->addClass('text-center'),
            Column::make('shop_name')->title('Supplier / Vendor Name'),
            Column::computed('contact')->title('Contact Info'),
            Column::computed('total_billed')->title('Total Invoiced')->addClass('text-right'),
            Column::computed('total_paid')->title('Total Paid')->addClass('text-right'),
            Column::computed('total_due')->title('Outstanding Due')->addClass('text-right'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(130)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'VendorLedger_' . date('YmdHis');
    }
}
