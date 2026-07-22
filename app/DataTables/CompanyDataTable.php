<?php

namespace App\DataTables;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CompanyDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('currency', function ($query) {
                return $query->currency->code ?? 'Base Currency';
            })
            ->addColumn('contact', function ($query) {
                $phone = $query->phone ? '<div><i class="fas fa-phone mr-1"></i> ' . e($query->phone) . '</div>' : '';
                $email = $query->email ? '<div class="text-muted small"><i class="fas fa-envelope mr-1"></i> ' . e($query->email) . '</div>' : '';
                return $phone . $email ?: '-';
            })
            ->addColumn('status', function ($query) {
                return $query->status
                    ? '<span class="badge badge-success px-3 py-1">Active</span>'
                    : '<span class="badge badge-danger px-3 py-1">Inactive</span>';
            })
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.master.companies.edit', $query->id) . "' class='btn btn-primary btn-sm mr-1'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.master.companies.destroy', $query->id) . "' class='btn btn-danger btn-sm delete-item'><i class='fas fa-trash'></i></a>";
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['contact', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(Company $model): QueryBuilder
    {
        return $model->newQuery()->with('currency');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('company-table')
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
            Column::make('name')->title('Company Name'),
            Column::make('vat_number')->title('VAT / Tax ID'),
            Column::computed('contact')->title('Phone / Email'),
            Column::computed('currency')->title('Currency'),
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
        return 'Company_' . date('YmdHis');
    }
}
