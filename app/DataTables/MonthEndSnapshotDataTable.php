<?php

namespace App\DataTables;

use App\Models\MonthEndSnapshot;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MonthEndSnapshotDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('image', function ($query) {
                $url = $query->product && $query->product->thumb_image ? asset('storage/'.$query->product->thumb_image) : asset('uploads/default.jpg');
                return '<img src="'.$url.'" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
            })
            ->addColumn('product_name', function ($query) {
                return $query->product->name ?? 'Deleted';
            })
            ->addColumn('period_badge', function ($query) {
                return '<span class="badge badge-primary">' . e($query->period) . '</span>';
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
            ->addColumn('closing_qty', function ($query) {
                return number_format($query->closing_qty, 2);
            })
            ->addColumn('closing_value', function ($query) {
                return \App\Models\GeneralSetting::first()->currency_icon . number_format($query->closing_value, 2);
            })
            ->filterColumn('product_name', function($query, $keyword) {
                $query->whereHas('product', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['image', 'period_badge', 'outlet'])
            ->setRowId('id');
    }

    public function query(MonthEndSnapshot $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['product', 'variant', 'outlet']);

        if (request()->filled('period')) {
            $query->where('period', request()->get('period'));
        }

        if (request()->filled('product_id')) {
            $query->where('product_id', request()->get('product_id'));
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('month-end-snapshots-table')
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
            Column::make('period_badge')->title('Period')->name('period'),
            Column::make('image')->title('Image')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('product_name')->title('Product')->name('product.name'),
            Column::make('outlet')->title('Warehouse'),
            Column::make('closing_qty')->title('Closing Qty')->addClass('text-center font-weight-bold'),
            Column::make('closing_value')->title('Closing Value (FIFO)')->addClass('text-right font-weight-bold'),
        ];
    }
}
