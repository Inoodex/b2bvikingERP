<?php

namespace App\DataTables;

use App\Models\GoodsReceipt;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GrnDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('grn_no_formatted', function ($row) {
                return '<a href="' . route('admin.goods-receipts.show', $row->id) . '" class="font-weight-bold"><code>' . e($row->grn_no) . '</code></a>';
            })
            ->addColumn('po_no', function ($row) {
                return '<a href="' . route('admin.purchase-orders.show', $row->purchase_id) . '" target="_blank">' . ($row->purchase?->po_no ?? 'PO #' . $row->purchase_id) . '</a>';
            })
            ->addColumn('outlet_name', function ($row) {
                return e($row->outlet?->name ?? 'N/A');
            })
            ->addColumn('received_by_name', function ($row) {
                return e($row->receivedBy?->name ?? 'N/A');
            })
            ->addColumn('received_date', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y, h:i A') : 'N/A';
            })
            ->addColumn('qc_badge', function ($row) {
                return $row->qc_status_badge;
            })
            ->addColumn('action', function ($row) {
                $viewBtn = '<a href="' . route('admin.goods-receipts.show', $row->id) . '" class="btn btn-sm btn-info mr-1" title="View GRN Details"><i class="fas fa-eye"></i> View</a>';
                $billBtn = '<a href="' . route('admin.vendor-bills.create', ['grn_id' => $row->id]) . '" class="btn btn-sm btn-success mr-1" title="Generate Vendor Bill from GRN"><i class="fas fa-file-invoice-dollar"></i> Bill</a>';
                $pdfBtn = '<a href="' . route('admin.goods-receipts.pdf', $row->id) . '" target="_blank" class="btn btn-sm btn-secondary mr-1" title="Download PDF"><i class="fas fa-file-pdf"></i> PDF</a>';
                
                $returnBtn = '';
                if (in_array($row->qc_status, ['partial', 'failed'])) {
                    if ($row->vendorReturn) {
                        $returnBtn = '<a href="' . route('admin.vendor-returns.show', $row->vendorReturn->id) . '" class="btn btn-sm btn-warning" title="View Issued Debit Note"><i class="fas fa-file-invoice-dollar"></i> View Debit Note</a>';
                    } else {
                        $returnBtn = '<a href="' . route('admin.vendor-returns.create', ['grn_id' => $row->id]) . '" class="btn btn-sm btn-warning" title="Process Return & Debit Note"><i class="fas fa-undo"></i> Return</a>';
                    }
                }

                return $viewBtn . $billBtn . $pdfBtn . $returnBtn;
            })
            ->rawColumns(['grn_no_formatted', 'po_no', 'qc_badge', 'action'])
            ->setRowId('id');
    }

    public function query(GoodsReceipt $model): QueryBuilder
    {
        return $model->newQuery()->with(['purchase', 'outlet', 'receivedBy', 'vendorReturn']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('grn-table')
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
            Column::computed('grn_no_formatted')->title('GRN Number'),
            Column::computed('po_no')->title('PO Reference'),
            Column::computed('outlet_name')->title('Destination Outlet'),
            Column::computed('received_by_name')->title('Received By'),
            Column::computed('received_date')->title('Receiving Date'),
            Column::computed('qc_badge')->title('QC Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(180)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'GRNs_' . date('YmdHis');
    }
}
