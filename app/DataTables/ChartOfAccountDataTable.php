<?php

namespace App\DataTables;

use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ChartOfAccountDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('account_code', function ($row) {
                $codeBadge = '<span class="badge badge-dark px-2 py-1 font-weight-bold font-monospace">' . e($row->account_code) . '</span>';
                if ($row->isSystemProtected()) {
                    $codeBadge .= ' <span class="badge badge-warning text-dark ml-1" title="Core System Account"><i class="fas fa-lock text-dark"></i> Core</span>';
                }
                return $codeBadge;
            })
            ->editColumn('account_name', function ($row) {
                $icon = $row->is_group ? '<i class="fas fa-folder text-warning mr-2"></i>' : '<i class="fas fa-file-invoice text-primary mr-2 ml-2"></i>';
                $style = $row->is_group ? 'font-weight-bold text-dark' : 'text-body font-weight-500';
                return '<span class="' . $style . '">' . $icon . e($row->account_name) . '</span>';
            })
            ->editColumn('account_type', function ($row) {
                $badges = [
                    'asset' => '<span class="badge badge-success px-2 py-1"><i class="fas fa-coins mr-1"></i> Asset (1000s)</span>',
                    'liability' => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-hand-holding-usd mr-1"></i> Liability (2000s)</span>',
                    'equity' => '<span class="badge badge-info px-2 py-1"><i class="fas fa-balance-scale mr-1"></i> Equity (3000s)</span>',
                    'revenue' => '<span class="badge badge-primary px-2 py-1"><i class="fas fa-chart-line mr-1"></i> Revenue (4000s)</span>',
                    'expense' => '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-receipt mr-1"></i> Expense (5000s)</span>',
                ];
                return $badges[$row->account_type] ?? '<span class="badge badge-secondary">' . e($row->account_type) . '</span>';
            })
            ->addColumn('live_balance', function ($row) {
                $balance = $row->balance;
                $formatted = 'kr. ' . number_format(abs($balance), 2);
                if ($balance > 0) {
                    return '<span class="badge badge-light border border-success text-success font-weight-bold px-2 py-1">' . $formatted . '</span>';
                } elseif ($balance < 0) {
                    return '<span class="badge badge-light border border-danger text-danger font-weight-bold px-2 py-1">(' . $formatted . ')</span>';
                }
                return '<span class="badge badge-light border text-muted px-2 py-1">kr. 0.00</span>';
            })
            ->editColumn('normal_balance', function ($row) {
                return '<span class="badge badge-light border text-uppercase font-weight-bold text-secondary">' . e($row->normal_balance) . '</span>';
            })
            ->addColumn('parent_name', function ($row) {
                return $row->parent ? '<span class="text-dark font-weight-500">' . e($row->parent->account_code . ' - ' . $row->parent->account_name) . '</span>' : '<span class="text-muted small">Top Level Group</span>';
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active
                    ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Active</span>'
                    : '<span class="badge badge-secondary px-2 py-1">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="btn-group btn-group-sm" role="group">';
                $btn .= '<a href="' . route('admin.chart-of-accounts.edit', $row->id) . '" class="btn btn-outline-primary font-weight-bold" title="Edit Head"><i class="fas fa-edit"></i> Edit</a>';
                if (!$row->isSystemProtected()) {
                    $btn .= '<a href="' . route('admin.chart-of-accounts.destroy', $row->id) . '" class="btn btn-outline-danger delete-item" title="Delete Head"><i class="fas fa-trash"></i></a>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['account_code', 'account_name', 'account_type', 'live_balance', 'normal_balance', 'parent_name', 'is_active', 'action'])
            ->setRowId('id');
    }

    public function query(ChartOfAccount $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['parent', 'journalLines']);

        if (request()->filled('type')) {
            $query->where('account_type', request('type'));
        }

        return $query->orderBy('account_code', 'asc');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('chartofaccount-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'asc')
                    ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::make('account_code')->title('Code')->width(110),
            Column::make('account_name')->title('Account Head Name'),
            Column::make('account_type')->title('Classification')->width(150),
            Column::computed('live_balance')->title('GL Live Balance')->width(140)->addClass('text-right'),
            Column::make('normal_balance')->title('Normal')->width(90)->addClass('text-center'),
            Column::computed('parent_name')->title('Parent Group'),
            Column::make('is_active')->title('Status')->width(90)->addClass('text-center'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(110)
                  ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'ChartOfAccount_' . date('YmdHis');
    }
}
