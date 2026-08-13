<?php

namespace App\DataTables;

use App\Models\DocumentSequence;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DocumentSequenceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<DocumentSequence> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('model_type_label', function ($query) {
                return '<div class="d-flex align-items-center"><i class="fas fa-file-invoice mr-2 text-primary"></i> <span class="font-weight-bold text-dark">' . e($query->model_type) . '</span></div>';
            })
            ->addColumn('prefix_badge', function ($query) {
                return '<span class="badge badge-dark px-3 py-2" style="background: #0f172a; color: #38bdf8; border-radius: 8px; font-weight: 700; font-family: monospace;">' . e($query->prefix) . '</span>';
            })
            ->addColumn('date_format_badge', function ($query) {
                if ($query->include_date) {
                    return '<span class="badge badge-light border text-dark font-weight-semibold">' . e($query->date_format) . ' (' . date($query->date_format) . ')</span>';
                }
                return '<span class="badge badge-light text-muted">Disabled</span>';
            })
            ->addColumn('padding_label', function ($query) {
                return '<span class="font-weight-semibold">' . $query->padding . ' digits</span>';
            })
            ->addColumn('next_number_badge', function ($query) {
                return '<span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 6px; font-size: 0.85rem;">#' . $query->next_number . '</span>';
            })
            ->addColumn('reset_policy_badge', function ($query) {
                return '<span class="badge badge-warning px-3 py-1 text-capitalize font-weight-bold" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">' . e($query->reset_policy) . '</span>';
            })
            ->addColumn('sample_preview', function ($query) {
                $numberStr = str_pad((string) $query->next_number, $query->padding, '0', STR_PAD_LEFT);
                $dateStr = $query->include_date ? date($query->date_format) . '-' : '';
                $sample = ($query->prefix ?? '') . $dateStr . $numberStr . ($query->suffix ?? '');
                return '<span class="px-3 py-1 rounded font-weight-bold" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-family: \'JetBrains Mono\', monospace; font-size: 0.9rem;">' . e($sample) . '</span>';
            })
            ->addColumn('action', function ($query) {
                return '<button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#editModal' . $query->id . '" style="border-radius: 8px; font-weight: 600; padding: 6px 12px;" title="Edit Sequence"><i class="fas fa-edit"></i></button>';
            })
            ->rawColumns([
                'model_type_label',
                'prefix_badge',
                'date_format_badge',
                'padding_label',
                'next_number_badge',
                'reset_policy_badge',
                'sample_preview',
                'action',
            ])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(DocumentSequence $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('document-sequence-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('model_type_label')->title('Document Module'),
            Column::computed('prefix_badge')->title('Prefix'),
            Column::computed('date_format_badge')->title('Date Format'),
            Column::computed('padding_label')->title('Padding'),
            Column::computed('next_number_badge')->title('Next Serial'),
            Column::computed('reset_policy_badge')->title('Reset Policy'),
            Column::computed('sample_preview')->title('Live Sample Preview'),
            Column::computed('action')->title('Action')->addClass('text-right'),
        ];
    }
}
