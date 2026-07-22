<?php

namespace App\DataTables;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CurrencyDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('is_base', function ($query) {
                return $query->is_base
                    ? '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Base</span>'
                    : '<span class="badge badge-secondary">Secondary</span>';
            })
            ->addColumn('status', function ($query) {
                return $query->status
                    ? '<span class="badge badge-success px-3 py-1">Active</span>'
                    : '<span class="badge badge-danger px-3 py-1">Inactive</span>';
            })
            ->addColumn('exchange_rate', function ($query) {
                return number_format($query->exchange_rate, 4);
            })
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.master.currencies.edit', $query->id) . "' class='btn btn-primary btn-sm mr-1'><i class='far fa-edit'></i></a>";
                $deleteBtn = '';
                if (!$query->is_base) {
                    $deleteBtn = "<a href='" . route('admin.master.currencies.destroy', $query->id) . "' class='btn btn-danger btn-sm delete-item'><i class='fas fa-trash'></i></a>";
                }
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['is_base', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(Currency $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('currency-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
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
            Column::make('id')->width(50),
            Column::make('code')->title('Code'),
            Column::make('name')->title('Currency Name'),
            Column::make('symbol')->title('Symbol'),
            Column::make('exchange_rate')->title('Exchange Rate'),
            Column::make('is_base')->title('Base Flag'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Currency_' . date('YmdHis');
    }
}
