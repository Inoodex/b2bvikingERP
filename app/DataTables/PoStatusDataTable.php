<?php

namespace App\DataTables;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PoStatusDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('po_no', fn($row) => '<a href="' . route('admin.purchase-orders.show', $row->id) . '" class="font-weight-bold text-primary" target="_blank"><code>' . e($row->po_no) . '</code></a>')
            ->editColumn('date', fn($row) => $row->date ? $row->date->format('d M Y') : 'N/A')
            ->addColumn('vendor_name', function($row) {
                if ($row->vendor_id) {
                    return '<a href="' . route('admin.vendor-ledger.show', $row->vendor_id) . '" class="font-weight-bold text-dark" target="_blank" title="View Vendor Ledger">' . e($row->vendor?->shop_name ?? $row->vendor?->name ?? 'N/A') . '</a>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('type_badge', fn($row) => '<span class="badge badge-' . ($row->purchase_type == 'foreign' ? 'info' : 'secondary') . '">' . ucfirst($row->purchase_type ?? 'local') . '</span>')
            ->editColumn('total_amount', fn($row) => formatConverted($row->total_amount))
            ->addColumn('milestone_badge', function($row) {
                $status = $row->milestone_status ?? 'draft';
                $badge = match($status) {
                    'goods_received' => 'success',
                    'shipped' => 'info',
                    'lc_opened' => 'warning',
                    'approved' => 'primary',
                    'po_sent' => 'secondary',
                    default => 'light text-dark',
                };
                return '<span class="badge badge-' . $badge . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
            })
            ->addColumn('payment_badge', fn($row) => '<span class="badge badge-' . ($row->payment_status == 'paid' ? 'success' : ($row->payment_status == 'partial' ? 'warning' : 'danger')) . '">' . ucfirst($row->payment_status ?? 'unpaid') . '</span>')
            ->addColumn('action', fn($row) => '<a href="' . route('admin.purchase-orders.show', $row->id) . '" class="btn btn-sm btn-info shadow-sm" target="_blank" title="View Procurement PO Details"><i class="fas fa-eye"></i> View PO</a>')
            ->rawColumns(['po_no', 'vendor_name', 'type_badge', 'milestone_badge', 'payment_badge', 'action'])
            ->setRowId('id');
    }

    public function query(Purchase $model): QueryBuilder
    {
        $request = request();
        $query = $model->newQuery()->with('vendor');

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

        return $query->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('po-status-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) { d.start_date = $("input[name=\"start_date\"]").val(); d.end_date = $("input[name=\"end_date\"]").val(); d.vendor_id = $("select[name=\"vendor_id\"]").val(); d.purchase_type = $("select[name=\"purchase_type\"]").val(); d.milestone_status = $("select[name=\"milestone_status\"]").val(); }'
            ])
            ->stateSave(false)
            ->pageLength(10)
            ->responsive(true)
            ->autoWidth(false)
            ->orderBy(0, 'desc')
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
            Column::make('po_no')->title('PO Number'),
            Column::make('date')->title('PO Date'),
            Column::computed('vendor_name')->title('Supplier')->orderable(false)->searchable(false),
            Column::computed('type_badge')->title('Type')->orderable(false)->searchable(false),
            Column::make('total_amount')->title('Total Amount')->addClass('text-right'),
            Column::computed('milestone_badge')->title('Milestone Status')->orderable(false)->searchable(false),
            Column::computed('payment_badge')->title('Payment Status')->orderable(false)->searchable(false),
            Column::computed('action')->title('Action')->addClass('text-center')->orderable(false)->searchable(false),
        ];
    }
}
