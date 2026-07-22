<?php

namespace App\DataTables;

use App\Models\Category;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param mixed $query Results from query() method.
     */
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $edit = '<a href="' . route('admin.category.edit', $query->id) . '" class="btn btn-primary"><i class="fas fa-edit"></i></a>';
                $delete = '<a href="' . route('admin.category.destroy', $query->id) . '" class="btn btn-danger delete-item ml-2"><i class="fas fa-trash"></i></a>';
                return $edit . $delete;
            })
            ->addColumn('image', function ($query) {
                return $query->image ? '<img src="' . asset($query->image) . '" width="100px" class="img-thumbnail">' : '<img src="' . asset('uploads/default.png') . '" width="100px" class="img-thumbnail">';
            })
            ->addColumn('status', function ($query) {
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->addColumn('frontend_show', function ($query) {
                $checked = $query->frontend_show ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-frontend-show" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->rawColumns(['action', 'status', 'frontend_show', 'image'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Category $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('category-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->stateSave(true)
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
            // Column::make('id')->width(50),
            // Column::make('image')->width(150),
            Column::make('name')->title('Category Name')->addClass('text-center'),
            Column::make('status')->addClass('text-center'),
            Column::make('frontend_show')->title('Show Home')->addClass('text-center'),
            Column::computed('action')->addClass('text-center')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Category_' . date('YmdHis');
    }
}
