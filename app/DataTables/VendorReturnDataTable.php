<?php

namespace App\DataTables;

use App\Models\VendorReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VendorReturnDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('return_no_formatted', function ($row) {
                return '<a href="' . route('admin.vendor-returns.show', $row->id) . '" class="font-weight-bold"><code>' . e($row->return_no) . '</code></a>';
            })
            ->addColumn('debit_note_no', function ($row) {
                return $row->debit_note_no ? '<strong class="text-danger"><code>' . e($row->debit_note_no) . '</code></strong>' : '<span class="text-muted">Pending</span>';
            })
            ->addColumn('po_no', function ($row) {
                return '<a href="' . route('admin.purchase-orders.show', $row->purchase_id) . '" target="_blank">' . ($row->purchase?->po_no ?? 'PO #' . $row->purchase_id) . '</a>';
            })
            ->addColumn('grn_no', function ($row) {
                return $row->goodsReceipt ? '<a href="' . route('admin.goods-receipts.show', $row->goods_receipt_id) . '" target="_blank"><code>' . e($row->goodsReceipt->grn_no) . '</code></a>' : 'N/A';
            })
            ->addColumn('reason_text', function ($row) {
                return e($row->reason ?? 'QC Rejection');
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('action', function ($row) {
                $viewBtn = '<a href="' . route('admin.vendor-returns.show', $row->id) . '" class="btn btn-sm btn-info mr-1" title="View Return & Debit Note"><i class="fas fa-eye"></i> View</a>';
                return $viewBtn;
            })
            ->rawColumns(['return_no_formatted', 'debit_note_no', 'po_no', 'grn_no', 'status_badge', 'action'])
            ->setRowId('id');
    }

    public function query(VendorReturn $model): QueryBuilder
    {
        return $model->newQuery()->with(['purchase', 'goodsReceipt', 'approvedBy']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('vendorreturn-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'desc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('print'),
                        // Button::make('reset'),
                        // Button::make('reload')
                    ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50),
            Column::computed('return_no_formatted')->title('Return Number'),
            Column::computed('debit_note_no')->title('Debit Note No'),
            Column::computed('po_no')->title('PO Reference'),
            Column::computed('grn_no')->title('GRN Reference'),
            Column::computed('reason_text')->title('Rejection Reason'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(100)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'VendorReturns_' . date('YmdHis');
    }
}
