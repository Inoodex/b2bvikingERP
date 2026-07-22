<?php

namespace App\DataTables;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DepartmentDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('company', function ($query) {
                return $query->company->name ?? 'All Companies';
            })
            ->addColumn('manager', function ($query) {
                return $query->manager->name ?? 'Unassigned';
            })
            ->addColumn('status', function ($query) {
                return $query->status
                    ? '<span class="badge badge-success px-3 py-1">Active</span>'
                    : '<span class="badge badge-danger px-3 py-1">Inactive</span>';
            })
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.master.departments.edit', $query->id) . "' class='btn btn-primary btn-sm mr-1'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.master.departments.destroy', $query->id) . "' class='btn btn-danger btn-sm delete-item'><i class='fas fa-trash'></i></a>";
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['status', 'action'])
            ->setRowId('id');
    }

    public function query(Department $model): QueryBuilder
    {
        return $model->newQuery()->with(['company', 'manager']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('department-table')
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
            Column::make('name')->title('Department Name'),
            Column::computed('company')->title('Company'),
            Column::computed('manager')->title('Manager'),
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
        return 'Department_' . date('YmdHis');
    }
}
