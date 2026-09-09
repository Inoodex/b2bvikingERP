<?php

namespace App\DataTables;

use App\Models\SystemBackupLog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BackupDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<SystemBackupLog> $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('file_name', function ($row) {
                return '<span class="badge badge-dark px-2 py-1 font-weight-bold" style="font-family: monospace;">' . e($row->file_name) . '</span>';
            })
            ->editColumn('file_size_bytes', function ($row) {
                $bytes = (int) $row->file_size_bytes;
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    return number_format($bytes / 1024, 2) . ' KB';
                }
                return $bytes . ' B';
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'completed') {
                    return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Ready</span>';
                }
                return '<span class="badge badge-warning">' . e(ucfirst($row->status)) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return optional($row->created_at)->format('d M, Y h:i:s A');
            })
            ->addColumn('action', function ($row) {
                $downloadUrl = route('admin.backups.download', $row->id);
                $deleteUrl = route('admin.backups.destroy', $row->id);

                return '
                    <a href="' . $downloadUrl . '" class="btn btn-sm btn-primary mr-1" title="Download SQL Dump">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                    <form action="' . $deleteUrl . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to permanently delete this backup file?\')">
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete Backup Archive">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                ';
            })
            ->rawColumns(['file_name', 'status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SystemBackupLog $model): QueryBuilder
    {
        return $model->newQuery()->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('backup-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                // Button::make('reset'),
                // Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::make('file_name')->title('Backup Archive File'),
            Column::make('file_size_bytes')->title('File Size'),
            Column::make('status')->title('Status'),
            Column::make('created_at')->title('Generated On'),
            Column::computed('action')->title('Actions')->exportable(false)->printable(false)->width(180)->addClass('text-center'),
        ];
    }
}
