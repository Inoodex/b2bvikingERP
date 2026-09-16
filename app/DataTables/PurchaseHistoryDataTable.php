<?php

namespace App\DataTables;

use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseHistoryDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('date', function ($row) {
                return $row->date ? Carbon::parse($row->date)->format('d M Y') : 'N/A';
            })
            ->editColumn('po_no', function ($row) {
                $poText = $row->po_no ?: ($row->invoice_no ?: ('PO-' . $row->id));
                $html = '<a href="' . route('admin.purchase-orders.show', $row->id) . '" class="font-weight-bold text-primary" target="_blank" title="View PO Details">' . e($poText) . '</a>';
                if ($row->po_no && $row->invoice_no) {
                    $html .= '<br><small class="text-muted"><i class="fas fa-receipt mr-1"></i>' . e($row->invoice_no) . '</small>';
                }
                return $html;
            })
            ->addColumn('vendor_name', function ($row) {
                $vendor = $row->vendor;
                if ($vendor) {
                    return '<a href="' . route('admin.vendor-ledger.show', $vendor->id) . '" class="font-weight-600 text-dark" target="_blank" title="View Vendor Ledger">' . e($vendor->shop_name ?? $vendor->name) . '</a>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('type_badge', function ($row) {
                $isForeign = ($row->purchase_type === 'foreign');
                return '<span class="badge badge-' . ($isForeign ? 'info' : 'secondary') . '">' . ucfirst($row->purchase_type ?? 'local') . '</span>';
            })
            ->addColumn('milestone_badge', function ($row) {
                $status = $row->milestone_status ?? 'draft';
                $badge = match ($status) {
                    'goods_received' => 'success',
                    'shipped'        => 'info',
                    'lc_opened'      => 'warning',
                    'approved'       => 'primary',
                    'po_sent'        => 'secondary',
                    default          => 'light text-dark',
                };
                return '<span class="badge badge-' . $badge . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
            })
            ->addColumn('user_name', function ($row) {
                return e($row->user?->name ?? 'System');
            })
            ->addColumn('items_count', function ($row) {
                return '<span class="badge badge-light border font-weight-bold">' . ($row->details_count ?? 0) . '</span>';
            })
            ->editColumn('total_amount', function ($row) {
                return '<strong class="text-dark">' . formatConverted($row->total_amount) . '</strong>';
            })
            ->addColumn('payment_badge', function ($row) {
                $payStatus = $row->payment_status ?? 'unpaid';
                $badge = match ($payStatus) {
                    'paid'    => 'success',
                    'partial' => 'warning',
                    default   => 'danger',
                };
                return '<span class="badge badge-' . $badge . '">' . ucfirst($payStatus) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('admin.purchase-orders.show', $row->id) . '" class="btn btn-info btn-sm shadow-sm" target="_blank" title="View Procurement PO Details"><i class="fas fa-eye"></i> View PO</a>';
            })
            ->rawColumns(['po_no', 'vendor_name', 'type_badge', 'milestone_badge', 'items_count', 'total_amount', 'payment_badge', 'action'])
            ->setRowId('id');
    }

    public function query(Purchase $model): QueryBuilder
    {
        $request = request();
        $query = $model->newQuery()->with(['vendor', 'user'])->withCount('details');

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('purchase_type')) {
            $query->where('purchase_type', $request->purchase_type);
        }

        if ($request->filled('milestone_status')) {
            $query->where('milestone_status', $request->milestone_status);
        }

        return $query->latest('date')->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('purchase-history-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    d.start_date = $("input[name=\"start_date\"]").val();
                    d.end_date = $("input[name=\"end_date\"]").val();
                    d.vendor_id = $("select[name=\"vendor_id\"]").val();
                    d.purchase_type = $("select[name=\"purchase_type\"]").val();
                    d.milestone_status = $("select[name=\"milestone_status\"]").val();
                }'
            ])
            ->stateSave(false)
            ->pageLength(10)
            ->responsive(true)
            ->autoWidth(false)
            ->parameters([
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ]
            ])
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('date')->title('Date'),
            Column::make('po_no')->title('PO / Invoice No'),
            Column::computed('vendor_name')->title('Vendor')->orderable(false)->searchable(false),
            Column::computed('type_badge')->title('Type')->orderable(false)->searchable(false),
            Column::computed('milestone_badge')->title('Milestone')->orderable(false)->searchable(false),
            Column::computed('user_name')->title('Created By')->orderable(false)->searchable(false),
            Column::computed('items_count')->title('Items')->addClass('text-center')->orderable(false)->searchable(false),
            Column::make('total_amount')->title('Base Total')->addClass('text-right'),
            Column::computed('payment_badge')->title('Payment')->addClass('text-center')->orderable(false)->searchable(false),
            Column::computed('action')->title('Action')->addClass('text-center')->orderable(false)->searchable(false),
        ];
    }
}
