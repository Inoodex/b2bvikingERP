<?php

namespace App\DataTables;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TaxDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Tax> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('type_label', function ($query) {
                return ucfirst((string) $query->type);
            })
            ->addColumn('value_label', function ($query) {
                $value = number_format((float) $query->value, 2);
                return $query->type === 'percent' ? $value . ' %' : $value;
            })
            ->addColumn('default_badge', function ($query) {
                if ($query->is_default) {
                    return '<span class="badge badge-success">Default</span>';
                }

                return '<span class="badge badge-secondary">No</span>';
            })
            ->addColumn('status', function ($query) {
                if ($query->status == 1) {
                    $activeButton = '<label class="custom-switch mt-2">
                        <input type="checkbox" checked name="custom-switch-checkbox" class="custom-switch-input change-status" data-id="' . $query->id . '" >
                        <span class="custom-switch-indicator"></span>
                    </label>';
                } else {
                    $activeButton = '<label class="custom-switch mt-2">
                        <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input change-status" data-id="' . $query->id . '" >
                        <span class="custom-switch-indicator"></span>
                    </label>';
                }
                return $activeButton;
            })
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.taxes.edit', $query->id) . "' class='btn btn-primary'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.taxes.destroy', $query->id) . "' class='btn btn-danger ml-2 delete-item'><i class='fas fa-trash'></i></a>";

                $defaultBtn = '';
                if (!$query->is_default) {
                    $defaultBtn = "<a href='javascript:void(0)' class='btn btn-warning ml-2 set-default' data-id='" . $query->id . "' title='Set as Default'><i class='fas fa-star'></i></a>";
                }

                return $editBtn . $defaultBtn . $deleteBtn;
            })
            ->rawColumns(['default_badge', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Tax>
     */
    public function query(Tax $model): QueryBuilder
    {
        return $model->newQuery()->orderByDesc('is_default')->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('tax-table')
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

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('name'),
            Column::computed('type_label')->title('Type'),
            Column::computed('value_label')->title('Value'),
            Column::computed('default_badge')->title('Default'),
            Column::make('status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(260)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Tax_' . date('YmdHis');
    }
}

