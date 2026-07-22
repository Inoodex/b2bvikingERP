<?php

namespace App\DataTables;

use App\Models\Color;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ColorDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Color> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.colors.edit', $query->id) . "' class='btn btn-primary'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.colors.destroy', $query->id) . "' class='btn btn-danger ml-2 delete-item'><i class='fas fa-trash'></i></a>";
                return $editBtn . $deleteBtn;
            })
            ->addColumn('hex_preview', function ($query) {
                if ($query->hex_code) {
                    return '<div style="width: 30px; height: 30px; background-color: ' . $query->hex_code . '; border: 1px solid #ddd; border-radius: 4px;"></div>';
                }
                return '-';
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
            ->rawColumns(['hex_preview', 'action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Color>
     */
    public function query(Color $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('color-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
            Column::computed('hex_preview')
                ->title('Color Preview')
                ->exportable(false)
                ->printable(false),
            Column::make('hex_code')->title('Hex Code'),
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
        return 'Color_' . date('YmdHis');
    }
}
