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
            ->editColumn('po_no', fn($row) => '<code>' . e($row->po_no) . '</code>')
            ->editColumn('date', fn($row) => $row->date ? $row->date->format('d M Y') : 'N/A')
            ->addColumn('vendor_name', fn($row) => '<strong>' . e($row->vendor?->shop_name ?? $row->vendor?->name ?? 'N/A') . '</strong>')
            ->addColumn('type_badge', fn($row) => '<span class="badge badge-' . ($row->purchase_type == 'foreign' ? 'info' : 'secondary') . '">' . ucfirst($row->purchase_type ?? 'local') . '</span>')
            ->editColumn('total_amount', fn($row) => '$' . number_format($row->total_amount, 2))
            ->addColumn('milestone_badge', fn($row) => '<span class="badge badge-primary">' . ucfirst(str_replace('_', ' ', $row->milestone_status ?? 'issued')) . '</span>')
            ->addColumn('payment_badge', fn($row) => '<span class="badge badge-' . ($row->payment_status == 'paid' ? 'success' : ($row->payment_status == 'partial' ? 'warning' : 'danger')) . '">' . ucfirst($row->payment_status ?? 'unpaid') . '</span>')
            ->addColumn('action', fn($row) => '<a href="' . route('admin.purchase-orders.show', $row->id) . '" class="btn btn-sm btn-info" target="_blank"><i class="fas fa-eye"></i> View PO</a>')
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

        return $query->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('po-status-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
