<?php

namespace App\DataTables;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VendorDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Vendor> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.vendor.edit', $query->id) . "'class='btn btn-primary'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.vendor.destroy', $query->id) . "'class='btn btn-danger ml-2 delete-item'><i class='fas fa-trash'></i></i></a>";
                return $editBtn . $deleteBtn;
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
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Vendor>
     */
    public function query(Vendor $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('vendor-table')
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
            Column::make('shop_name')->title('Company Name'),
            Column::make('phone'),
            Column::make('email'),
            Column::make('address'),
            Column::make('country'),
            Column::make('status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(200)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Vendor_' . date('YmdHis');
    }
}
