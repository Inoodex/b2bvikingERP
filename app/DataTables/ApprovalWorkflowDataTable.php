<?php

namespace App\DataTables;

use App\Models\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ApprovalWorkflowDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('model_type', function ($query) {
                return '<span class="badge badge-info">' . e(class_basename($query->model_type)) . '</span>';
            })
            ->addColumn('amount_range', function ($query) {
                if ($query->min_amount || $query->max_amount) {
                    $min = formatWithCurrency($query->min_amount ?? 0);
                    $max = $query->max_amount ? formatWithCurrency($query->max_amount) : 'Unlimited';
                    return $min . ' - ' . $max;
                }
                return '<span class="badge badge-light">All Amounts</span>';
            })
            ->addColumn('steps', function ($query) {
                $html = '';
                foreach ($query->steps as $step) {
                    $html .= '<span class="badge badge-primary mr-1">Step ' . $step->step_order . ': ' . e($step->step_name) . '</span>';
                }
                return $html ?: '<span class="text-muted">No steps</span>';
            })
            ->addColumn('status', function ($query) {
                return $query->status
                    ? '<span class="badge badge-success px-3 py-1">Active</span>'
                    : '<span class="badge badge-danger px-3 py-1">Inactive</span>';
            })
            ->addColumn('action', function ($query) {
                $editBtn = "<a href='" . route('admin.master.approval-workflows.edit', $query->id) . "' class='btn btn-primary btn-sm mr-1'><i class='far fa-edit'></i></a>";
                $deleteBtn = "<a href='" . route('admin.master.approval-workflows.destroy', $query->id) . "' class='btn btn-danger btn-sm delete-item'><i class='fas fa-trash'></i></a>";
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['model_type', 'amount_range', 'steps', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(ApprovalWorkflow $model): QueryBuilder
    {
        return $model->newQuery()->with('steps.approverUser');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('approvalworkflow-table')
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
            Column::make('name')->title('Workflow Name'),
            Column::computed('model_type')->title('Target Module'),
            Column::computed('amount_range')->title('Amount Range'),
            Column::computed('steps')->title('Steps Chain'),
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
        return 'ApprovalWorkflow_' . date('YmdHis');
    }
}
