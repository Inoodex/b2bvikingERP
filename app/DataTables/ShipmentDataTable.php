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
            ->addColumn('transport_mode', function ($row) {
                return $row->transport_badge;
            })
            ->addColumn('supplier', function ($row) {
                return '<strong>' . e($row->purchase?->vendor?->name ?? 'N/A') . '</strong>';
            })
            ->addColumn('carrier_identifier', function ($row) {
                $carrier = $row->carrier_name ? '<span class="text-primary font-weight-bold">' . e($row->carrier_name) . '</span><br>' : '';
                $identifier = $row->vessel_or_flight ? '<span><i class="' . $row->transport_icon . ' text-muted mr-1"></i> ' . e($row->vessel_or_flight) . '</span>' : '<span class="text-muted">N/A</span>';
                $container = $row->container_no ? '<br><small class="text-muted"><i class="fas fa-box"></i> ' . e($row->container_no) . '</small>' : '';
                return $carrier . $identifier . $container;
            })
            ->addColumn('bl_awb', function ($row) {
                if (!$row->bl_awb_no) {
                    return '<span class="text-muted">N/A</span>';
                }
                $badgeType = match ($row->transport_mode) {
                    'air' => 'badge-info',
                    'road' => 'badge-warning',
                    'rail' => 'badge-secondary',
                    default => 'badge-primary',
                };
                return '<code>' . e($row->bl_awb_no) . '</code>';
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
                    $editBtn = '<a href="' . route('admin.shipments.edit', $row->id) . '" class="btn btn-sm btn-primary mr-1" title="Edit Shipment"><i class="fas fa-edit"></i></a>';
                }

                $deleteBtn = '';
                if ($row->goodsReceiptsCount() == 0) {
                    $deleteBtn = '<a href="' . route('admin.shipments.destroy', $row->id) . '" class="btn btn-sm btn-danger delete-item" title="Delete Shipment"><i class="fas fa-trash"></i></a>';
                }
                
                return '<div class="btn-group" role="group">' . $viewBtn . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['po_no', 'transport_mode', 'supplier', 'carrier_identifier', 'bl_awb', 'ports', 'etd_eta', 'status_badge', 'action'])
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
                    ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->width(50),
            Column::computed('po_no')->title('PO Reference'),
            Column::computed('transport_mode')->title('Mode'),
            Column::computed('supplier')->title('Supplier'),
            Column::computed('carrier_identifier')->title('Carrier & Vessel / Flight'),
            Column::computed('bl_awb')->title('BL / AWB No'),
            Column::computed('ports')->title('Route (Origin &rarr; Dest)'),
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
