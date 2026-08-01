<?php

namespace App\DataTables;

use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ShipmentDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('po_no', function ($row) {
                return '<a href="' . route('admin.purchase-orders.show', $row->purchase_id) . '" class="font-weight-bold" target="_blank">' . ($row->purchase?->po_no ?? 'PO #' . $row->purchase_id) . '</a>';
            })
            ->addColumn('supplier', function ($row) {
                return '<strong>' . e($row->purchase?->vendor?->name ?? 'N/A') . '</strong>';
            })
            ->addColumn('vessel_container', function ($row) {
                $vessel = $row->vessel_or_flight ? '<strong>' . e($row->vessel_or_flight) . '</strong>' : 'N/A';
                $container = $row->container_no ? '<br><small class="text-muted"><i class="fas fa-box"></i> ' . e($row->container_no) . '</small>' : '';
                return $vessel . $container;
            })
            ->addColumn('bl_awb', function ($row) {
                return $row->bl_awb_no ? '<code>' . e($row->bl_awb_no) . '</code>' : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('ports', function ($row) {
                $loading = e($row->port_of_loading ?? 'N/A');
                $discharge = e($row->port_of_discharge ?? 'N/A');
                return '<small>' . $loading . ' &rarr; ' . $discharge . '</small>';
            })
            ->addColumn('etd_eta', function ($row) {
                $etd = $row->etd ? $row->etd->format('d M Y') : 'N/A';
                $eta = $row->eta ? $row->eta->format('d M Y') : 'N/A';
                return '<small>ETD: ' . $etd . '<br>ETA: ' . $eta . '</small>';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('action', function ($row) {
                $viewBtn = '<a href="' . route('admin.shipments.show', $row->id) . '" class="btn btn-sm btn-info mr-1" title="View Details"><i class="fas fa-eye"></i> View</a>';
                
                $editBtn = '';
                if ($row->status !== 'cancelled' && $row->goodsReceiptsCount() == 0) {
                    $editBtn = '<a href="' . route('admin.shipments.edit', $row->id) . '" class="btn btn-sm btn-primary mr-1" title="Edit Shipment"><i class="fas fa-edit"></i> </a>';
                }
                
                return $viewBtn . $editBtn;
            })
            ->rawColumns(['po_no', 'supplier', 'vessel_container', 'bl_awb', 'ports', 'etd_eta', 'status_badge', 'action'])
            ->setRowId('id');
    }

    public function query(Shipment $model): QueryBuilder
    {
        return $model->newQuery()->with(['purchase.vendor']);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('shipment-table')
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
            Column::computed('po_no')->title('PO Reference'),
            Column::computed('supplier')->title('Supplier'),
            Column::computed('vessel_container')->title('Vessel / Container'),
            Column::computed('bl_awb')->title('BL / AWB No'),
            Column::computed('ports')->title('Port Route'),
            Column::computed('etd_eta')->title('ETD / ETA'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(130)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Shipments_' . date('YmdHis');
    }
}
