<?php

namespace App\DataTables;

use Spatie\Permission\Models\Role;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RolesDataTable extends DataTable
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
                $edit = '<a href="' . route('admin.role.edit', $query->id) . '" class="btn btn-sm btn-primary" title="Edit Role"><i class="fas fa-edit"></i></a>';
                if ($query->name !== 'Admin') {
                    $delete = '<a href="' . route('admin.role.destroy', $query->id) . '" class="btn btn-sm btn-danger delete-item ml-1" title="Delete Role"><i class="fas fa-trash"></i></a>';
                    return $edit . $delete;
                }
                return $edit;
            })
            ->addColumn('permissions', function ($query) {
                $count = $query->permissions->count();
                if ($query->name === 'Admin' || $count >= 50) {
                    return '<div class="d-flex align-items-center flex-wrap" style="gap: 6px;">'
                         . '<span class="badge badge-success px-2 py-1" style="font-weight: 700; font-size: 11px; border-radius: 8px; background: linear-gradient(135deg, #10b981, #059669); color: #fff;"><i class="fas fa-crown mr-1 text-warning"></i> All Permissions (Full Access)</span>'
                         . '<span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px; border-radius: 8px;">' . $count . ' Granted</span>'
                         . '</div>';
                }

                if ($count === 0) {
                    return '<span class="badge badge-warning px-2 py-1" style="font-size: 11px; border-radius: 8px;">No Permissions</span>';
                }

                $showLimit = 5;
                $badges = '<div class="d-flex align-items-center flex-wrap" style="gap: 4px; max-width: 680px;">';
                foreach ($query->permissions->take($showLimit) as $permission) {
                    $badges .= '<span class="badge px-2 py-1" style="font-size: 11px; font-weight: 600; border-radius: 6px; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">' . e($permission->name) . '</span>';
                }
                if ($count > $showLimit) {
                    $remaining = $count - $showLimit;
                    $otherNames = $query->permissions->slice($showLimit)->pluck('name')->implode(', ');
                    $badges .= '<span class="badge badge-light border px-2 py-1" style="font-size: 11px; font-weight: 700; border-radius: 6px; cursor: pointer; color: #475569;" title="' . e($otherNames) . '" data-toggle="tooltip">+' . $remaining . ' more</span>';
                }
                $badges .= '<span class="badge badge-secondary ml-1 px-2 py-1" style="font-size: 10px; border-radius: 10px;">' . $count . ' total</span>';
                $badges .= '</div>';

                return $badges;
            })
            ->rawColumns(['action', 'permissions'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Role $model)
    {
        return $model->newQuery()->with('permissions');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('role-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->parameters([
                'responsive' => false,
                'autoWidth' => false,
                'pageLength' => 10,
            ])
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
            Column::make('id')->width(60)->addClass('text-center font-weight-bold'),
            Column::make('name')->width(180)->addClass('font-weight-bold'),
            Column::make('permissions')->title('Assigned Permissions'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(110)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Roles_' . date('YmdHis');
    }
}
