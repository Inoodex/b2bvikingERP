<?php

namespace App\DataTables;

use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GeneralLedgerDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('entry_date', function ($row) {
                return $row->journalEntry?->entry_date ? $row->journalEntry->entry_date->format('Y-m-d') : 'N/A';
            })
            ->editColumn('entry_no', function ($row) {
                return '<span class="badge badge-light border text-dark font-weight-bold"><i class="fas fa-file-alt text-primary mr-1"></i> ' . e($row->journalEntry?->entry_no) . '</span>';
            })
            ->addColumn('account_info', function ($row) {
                return '<strong class="text-dark">' . e($row->account?->account_code) . '</strong> — ' . e($row->account?->account_name);
            })
            ->addColumn('description', function ($row) {
                $ref = $row->journalEntry?->reference_type ? '<small class="text-muted d-block">Ref: ' . class_basename($row->journalEntry->reference_type) . ' #' . $row->journalEntry->reference_id . '</small>' : '';
                return e($row->journalEntry?->narration ?? 'N/A') . $ref;
            })
            ->editColumn('debit', function ($row) {
                return $row->debit > 0 ? '<strong class="text-success">kr. ' . number_format((float)$row->debit, 2) . '</strong>' : '—';
            })
            ->editColumn('credit', function ($row) {
                return $row->credit > 0 ? '<strong class="text-info">kr. ' . number_format((float)$row->credit, 2) . '</strong>' : '—';
            })
            ->rawColumns(['entry_no', 'account_info', 'description', 'debit', 'credit'])
            ->setRowId('id');
    }

    public function query(JournalEntryLine $model): QueryBuilder
    {
        $dateFrom = request('date_from', date('Y-01-01'));
        $dateTo = request('date_to', date('Y-m-d'));
        $selectedAccountId = request('account_id');

        $query = $model->newQuery()
            ->with(['account', 'journalEntry'])
            ->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                $q->whereDate('entry_date', '>=', $dateFrom)
                  ->whereDate('entry_date', '<=', $dateTo);
            });

        if ($selectedAccountId) {
            $query->where('account_id', $selectedAccountId);
        }

        return $query->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('generalledger-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'desc')
                    ->selectStyleSingle();
    }

    public function getColumns(): array
    {
        return [
            Column::computed('entry_date')->title('Entry Date')->width(110),
            Column::computed('entry_no')->title('Voucher No')->width(140),
            Column::computed('account_info')->title('Account Code & Name'),
            Column::computed('description')->title('Reference / Event Description'),
            Column::make('debit')->title('Debit (DR)')->width(130)->addClass('text-right'),
            Column::make('credit')->title('Credit (CR)')->width(130)->addClass('text-right'),
        ];
    }

    protected function filename(): string
    {
        return 'GeneralLedger_' . date('YmdHis');
    }
}
