<?php

namespace App\DataTables;

use App\Models\WarehouseZone;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class WarehouseZoneDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<WarehouseZone> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($row){
                $editBtn = '<a href="'.route('admin.warehouse-zones.edit', $row->id).'" class="btn btn-sm btn-primary mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
                $deleteBtn = '<a href="'.route('admin.warehouse-zones.destroy', $row->id).'" class="btn btn-sm btn-danger delete-item" title="Delete"><i class="fas fa-trash"></i></a>';
                return '<div class="btn-group" role="group">' . $editBtn . $deleteBtn . '</div>';
            })
            ->addColumn('outlet', function($row){
                return '<span class="font-weight-bold text-dark">' . e($row->outlet->name ?? 'N/A') . '</span>';
            })
            ->editColumn('type', function($row){
                if ($row->type === 'quarantine') {
                    return '<span class="badge badge-warning"><i class="fas fa-shield-alt mr-1"></i> Quarantine</span>';
                } elseif ($row->type === 'scrap') {
                    return '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Scrap / Damage</span>';
                }
                return '<span class="badge badge-primary"><i class="fas fa-check-circle mr-1"></i> Active Storage</span>';
            })
            ->editColumn('status', function($row){
                return $row->status 
                    ? '<span class="badge badge-success">Active</span>' 
                    : '<span class="badge badge-secondary">Inactive</span>';
            })
            ->rawColumns(['outlet', 'type', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<WarehouseZone>
     */
    public function query(WarehouseZone $model): QueryBuilder
    {
        return $model->newQuery()->with('outlet');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('warehousezone-table')
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

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#ID')->width(60)->addClass('text-center'),
            Column::make('outlet')->name('outlet.name')->title('Warehouse / Outlet'),
            Column::make('name')->title('Zone Name'),
            Column::make('type')->title('Zone Type')->addClass('text-center'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::computed('action')
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
        return 'WarehouseZone_' . date('YmdHis');
    }
}
