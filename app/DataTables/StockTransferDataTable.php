<?php

namespace App\DataTables;

use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockTransferDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('transfer_no', function ($query) {
                return '<a href="' . route('admin.stock-transfers.show', $query->id) . '" class="font-weight-bold text-primary">#' . e($query->transfer_no) . '</a>';
            })
            ->addColumn('from_outlet', function ($query) {
                return e($query->fromOutlet ? ($query->fromOutlet->outlet_name ?? $query->fromOutlet->name) : 'Central Warehouse');
            })
            ->addColumn('to_outlet', function ($query) {
                return e($query->toOutlet ? ($query->toOutlet->outlet_name ?? $query->toOutlet->name) : 'Outlet #' . $query->to_outlet_id);
            })
            ->addColumn('transfer_date', function ($query) {
                return optional($query->transfer_date)->format('d M, Y') ?: optional($query->created_at)->format('d M, Y');
            })
            ->addColumn('items_count', function ($query) {
                return '<span class="badge badge-info">' . (int)$query->items_count . ' Items</span>';
            })
            ->addColumn('status', function ($query) {
                if ($query->status === 'received') {
                    return '<span class="badge badge-success"><i class="fas fa-check-double mr-1"></i> Received</span>';
                } elseif ($query->status === 'dispatched') {
                    return '<span class="badge badge-primary"><i class="fas fa-truck mr-1"></i> In Transit</span>';
                } elseif ($query->status === 'cancelled') {
                    return '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i> Cancelled</span>';
                }
                return '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Draft</span>';
            })
            ->addColumn('action', function ($query) {
                $viewBtn = '<a href="' . route('admin.stock-transfers.show', $query->id) . '" class="btn btn-primary btn-sm mr-1" title="View Transfer"><i class="fas fa-eye"></i></a>';
                $pdfBtn = '<a href="' . route('admin.stock-transfers.pdf', $query->id) . '" target="_blank" class="btn btn-danger btn-sm mr-1" title="Challan PDF"><i class="fas fa-file-pdf"></i></a>';
                
                $receiveBtn = '';
                if ($query->status === 'dispatched') {
                    $receiveBtn = '<a href="' . route('admin.stock-transfers.receive-form', $query->id) . '" class="btn btn-success btn-sm mr-1" title="Receive Stock"><i class="fas fa-box-check"></i> Receive</a>';
                }

                return $viewBtn . $pdfBtn . $receiveBtn;
            })
            ->rawColumns(['action', 'transfer_no', 'from_outlet', 'to_outlet', 'items_count', 'status'])
            ->setRowId('id');
    }

    public function query(StockTransfer $model): QueryBuilder
    {
        return $model->newQuery()->with(['fromOutlet', 'toOutlet', 'requestedByUser'])->withCount('items')->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-transfers-table')
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
            Column::make('transfer_no')->title('Transfer No'),
            Column::make('from_outlet')->title('From Warehouse'),
            Column::make('to_outlet')->title('To Outlet / Branch'),
            Column::make('transfer_date')->title('Transfer Date'),
            Column::make('items_count')->title('Items'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }
}
