<?php

namespace App\DataTables;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OutletDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('type', function ($query) {
                if ($query->type === 'warehouse') {
                    return '<span class="badge badge-primary"><i class="fas fa-warehouse mr-1"></i> Central Warehouse</span>';
                } elseif ($query->type === 'retail') {
                    return '<span class="badge badge-info"><i class="fas fa-store mr-1"></i> Retail Store</span>';
                } else {
                    return '<span class="badge badge-warning"><i class="fas fa-truck-loading mr-1"></i> Wholesale Hub</span>';
                }
            })
            ->addColumn('company', function ($query) {
                return $query->company->name ?? 'Global';
            })
            ->addColumn('contact', function ($query) {
                $phone = $query->phone ? '<div><i class="fas fa-phone mr-1 text-muted"></i> ' . e($query->phone) . '</div>' : '';
                $email = $query->email ? '<div class="text-muted small"><i class="fas fa-envelope mr-1 text-muted"></i> ' . e($query->email) . '</div>' : '';
                return ($phone || $email) ? ($phone . $email) : '-';
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
                $editBtn = "<a href='" . route('admin.master.outlets.edit', $query->id) . "' class='btn btn-primary btn-sm mr-1'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.master.outlets.destroy', $query->id) . "' class='btn btn-danger btn-sm delete-item'><i class='fas fa-trash'></i></a>";
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['type', 'contact', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(Outlet $model): QueryBuilder
    {
        return $model->newQuery()->with(['company', 'manager']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('outlet-table')
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
            // Column::make('id')->width(50),
            Column::make('code')->title('Code'),
            Column::make('name')->title('Name'),
            Column::computed('type')->title('Type'),
            Column::computed('company')->title('Company'),
            Column::computed('contact')->title('Phone / Email'),
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
        return 'Outlet_' . date('YmdHis');
    }
}
