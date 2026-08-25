<?php

namespace App\DataTables;

use App\Models\StockBatch;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockBatchDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('received_date', function ($query) {
                return optional($query->received_date)->format('Y-m-d');
            })
            ->addColumn('image', function ($query) {
                $url = $query->product && $query->product->thumb_image ? asset('storage/'.$query->product->thumb_image) : asset('uploads/default.jpg');
                return '<img src="'.$url.'" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
            })
            ->addColumn('product_name', function ($query) {
                return $query->product->name ?? 'Deleted';
            })
            ->addColumn('batch_no', function ($query) {
                return '<span class="badge badge-info">' . e($query->batch_no) . '</span>';
            })
            ->addColumn('outlet', function ($query) {
                if ($query->outlet) {
                    $name = $query->outlet->name;
                    $outletName = $query->outlet->outlet_name;
                    $label = $name . ($outletName ? " ({$outletName})" : '');
                    return '<span class="badge badge-secondary">' . e($label) . '</span>';
                }
                return '<span class="badge badge-dark">Main Warehouse</span>';
            })
            ->addColumn('qty_received', function ($query) {
                return number_format($query->qty_received, 2);
            })
            ->addColumn('qty_remaining', function ($query) {
                $color = $query->qty_remaining > 0 ? 'text-success font-weight-bold' : 'text-danger';
                return '<span class="'.$color.'">' . number_format($query->qty_remaining, 2) . '</span>';
            })
            ->addColumn('unit_cost', function ($query) {
                return \App\Models\GeneralSetting::first()->currency_icon . number_format($query->unit_cost, 2);
            })
            ->addColumn('total_value', function ($query) {
                $total = $query->qty_remaining * $query->unit_cost;
                return \App\Models\GeneralSetting::first()->currency_icon . number_format($total, 2);
            })
            ->filterColumn('product_name', function($query, $keyword) {
                $query->whereHas('product', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['image', 'batch_no', 'outlet', 'qty_remaining'])
            ->setRowId('id');
    }

    public function query(StockBatch $model): QueryBuilder
    {
        return $model->newQuery()->with(['product', 'variant', 'outlet', 'goodsReceipt']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-batches-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel')->className('btn btn-primary btn-sm'),
                Button::make('csv')->className('btn btn-primary btn-sm'),
                Button::make('pdf')->className('btn btn-primary btn-sm'),
                Button::make('print')->className('btn btn-primary btn-sm'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('received_date')->title('Date Received'),
            Column::make('image')->title('Image')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('product_name')->title('Product')->name('product.name'),
            Column::make('batch_no')->title('Batch No'),
            Column::make('outlet')->title('Warehouse'),
            Column::make('qty_received')->title('Qty Rcvd')->addClass('text-center'),
            Column::make('qty_remaining')->title('Qty Remain')->addClass('text-center'),
            Column::make('unit_cost')->title('Unit Cost')->addClass('text-right'),
            Column::computed('total_value')->title('Batch Value')->addClass('text-right font-weight-bold'),
        ];
    }
}
