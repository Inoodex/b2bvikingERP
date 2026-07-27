<?php

namespace App\DataTables;

use App\Models\Rfq;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RfqDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('source', function ($query) {
                if (empty($query->source_type)) {
                    return '<span class="badge badge-secondary">Ad-hoc (Manual)</span>';
                } elseif ($query->source_type === 'App\Models\Order') {
                    return '<span class="badge badge-info">Order #'.$query->source_id.'</span>';
                } elseif ($query->source_type === 'App\Models\CustomProductRequest') {
                    return '<span class="badge badge-primary">Custom Req #'.$query->source_id.'</span>';
                } elseif ($query->source_type === 'App\Models\ProductRequest') {
                    return '<span class="badge badge-warning">Old PR #'.$query->source_id.'</span>';
                }
                return '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('vendors_count', function ($query) {
                return '<span class="badge badge-info">' . $query->vendors->count() . ' Vendors</span>';
            })
            ->addColumn('items_count', function ($query) {
                return '<span class="badge badge-dark">' . $query->items->count() . ' Items</span>';
            })
            ->addColumn('status_badge', function ($query) {
                $status = strtolower($query->status);
                $class = match($status) {
                    'draft' => 'badge-secondary',
                    'published' => 'badge-primary',
                    'closed' => 'badge-warning',
                    'awarded' => 'badge-success',
                    'cancelled' => 'badge-danger',
                    default => 'badge-secondary',
                };
                return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
            })
            ->editColumn('deadline', function ($query) {
                return $query->deadline ? \Carbon\Carbon::parse($query->deadline)->format('d M, Y') : 'N/A';
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at->format('d M, Y');
            })
            ->addColumn('action', function ($query) {
                $show = '<a href="' . route('admin.rfqs.show', $query->id) . '" class="btn btn-primary btn-sm mr-1" title="Details"><i class="fas fa-eye"></i></a>';
                return $show;
            })
            ->rawColumns(['source', 'vendors_count', 'items_count', 'status_badge', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Rfq $model): QueryBuilder
    {
        return $model->newQuery()->with(['items', 'vendors'])->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('rfq-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->stateSave(true)
            ->responsive(true)
            ->autoWidth(false)
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
            Column::make('id')->visible(false),
            Column::make('rfq_no')->title('RFQ No'),
            Column::computed('source')->title('Source'),
            Column::computed('items_count')->title('Items'),
            Column::computed('vendors_count')->title('Vendors'),
            Column::make('deadline')->title('Deadline'),
            Column::computed('status_badge')->title('Status'),
            Column::make('created_at')->title('Created At'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'RFQs_' . date('YmdHis');
    }
}
