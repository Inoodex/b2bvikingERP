<?php

namespace App\DataTables;

use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockAdjustmentDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('adjustment_no', function ($query) {
                return '<a href="' . route('admin.stock-adjustments.show', $query->id) . '" class="font-weight-bold text-primary">#' . e($query->adjustment_no) . '</a>';
            })
            ->addColumn('outlet', function ($query) {
                return e($query->outlet ? ($query->outlet->outlet_name ?? $query->outlet->name) : 'Central Warehouse');
            })
            ->addColumn('adjustment_type', function ($query) {
                if ($query->adjustment_type === 'increase') {
                    return '<span class="badge badge-success"><i class="fas fa-plus-circle mr-1"></i> Stock Increase</span>';
                } elseif ($query->adjustment_type === 'decrease') {
                    return '<span class="badge badge-danger"><i class="fas fa-minus-circle mr-1"></i> Stock Decrease</span>';
                }
                return '<span class="badge badge-info"><i class="fas fa-sync mr-1"></i> Reconciliation</span>';
            })
            ->addColumn('reason_code', function ($query) {
                $reasons = [
                    'damage' => '<span class="badge badge-warning">Damage Write-off</span>',
                    'physical_count' => '<span class="badge badge-secondary">Physical Count</span>',
                    'expired' => '<span class="badge badge-danger">Expired Goods</span>',
                    'sample_marketing' => '<span class="badge badge-info">Sample / Marketing</span>',
                    'theft_loss' => '<span class="badge badge-dark">Theft / Loss</span>',
                    'internal_use' => '<span class="badge badge-primary">Internal Consumption</span>',
                    'other' => '<span class="badge badge-light">Other</span>',
                ];
                return $reasons[$query->reason_code] ?? e(ucfirst(str_replace('_', ' ', $query->reason_code)));
            })
            ->addColumn('items_count', function ($query) {
                return '<span class="badge badge-dark">' . (int)$query->items_count . ' Items</span>';
            })
            ->addColumn('total_cost', function ($query) {
                return '<span class="font-weight-bold">' . number_format((float)$query->total_adjusted_cost, 2) . '</span>';
            })
            ->addColumn('status', function ($query) {
                if ($query->status === 'approved') {
                    return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Approved</span>';
                } elseif ($query->status === 'cancelled') {
                    return '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Cancelled</span>';
                }
                return '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Draft</span>';
            })
            ->addColumn('created_at', function ($query) {
                return optional($query->created_at)->format('d M, Y h:i A');
            })
            ->addColumn('action', function ($query) {
                $viewBtn = '<a href="' . route('admin.stock-adjustments.show', $query->id) . '" class="btn btn-primary btn-sm mr-1" title="View Details"><i class="fas fa-eye"></i></a>';
                return $viewBtn;
            })
            ->rawColumns(['action', 'adjustment_no', 'adjustment_type', 'reason_code', 'items_count', 'total_cost', 'status'])
            ->setRowId('id');
    }

    public function query(StockAdjustment $model): QueryBuilder
    {
        return $model->newQuery()->with(['outlet', 'requestedByUser'])->withCount('items')->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-adjustments-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                // Button::make('reset'),
                // Button::make('reload')
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('adjustment_no')->title('Adjustment No'),
            Column::make('outlet')->title('Warehouse / Outlet'),
            Column::make('adjustment_type')->title('Type'),
            Column::make('reason_code')->title('Reason'),
            Column::make('items_count')->title('Items'),
            Column::make('total_cost')->title('Total Cost'),
            Column::make('status')->title('Status'),
            Column::make('created_at')->title('Date'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('text-center'),
        ];
    }
}
