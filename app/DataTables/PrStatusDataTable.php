<?php

namespace App\DataTables;

use App\Models\ProductRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PrStatusDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('request_no', fn($row) => '<code>' . e($row->request_no ?? ('PR-' . $row->id)) . '</code>')
            ->addColumn('request_date', fn($row) => $row->created_at ? $row->created_at->format('d M Y') : 'N/A')
            ->addColumn('user_name', fn($row) => e($row->user?->name ?? 'N/A'))
            ->addColumn('dept_name', fn($row) => e($row->department?->name ?? 'N/A'))
            ->addColumn('items_count', fn($row) => '<span class="badge badge-danger">' . $row->items->count() . '</span>')
            ->addColumn('status_badge', fn($row) => '<span class="badge badge-warning">' . ucfirst($row->status) . '</span>')
            ->rawColumns(['request_no', 'request_date', 'user_name', 'dept_name', 'items_count', 'status_badge'])
            ->setRowId('id');
    }

    public function query(ProductRequest $model): QueryBuilder
    {
        $request = request();
        $query = $model->newQuery()->with(['user', 'department', 'items'])->whereIn('status', ['pending', 'draft']);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('pr-status-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->stateSave(false)
            ->pageLength(10)
            ->responsive(true)
            ->autoWidth(false)
            ->orderBy(0, 'desc')
            ->parameters([
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ]
            ])
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
            Column::make('request_no')->title('PR No'),
            Column::computed('request_date')->title('Request Date')->orderable(false)->searchable(false),
            Column::computed('user_name')->title('Requested By')->orderable(false)->searchable(false),
            Column::computed('dept_name')->title('Department')->orderable(false)->searchable(false),
            Column::computed('items_count')->title('Pending Line Items')->addClass('text-center')->orderable(false)->searchable(false),
            Column::computed('status_badge')->title('Status')->orderable(false)->searchable(false),
        ];
    }
}
