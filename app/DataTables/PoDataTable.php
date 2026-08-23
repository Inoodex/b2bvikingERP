<?php

namespace App\DataTables;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class PoDataTable extends \Yajra\DataTables\Services\DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('po_no', function ($query) {
                return '<a href="' . route('admin.purchase-orders.show', $query->id) . '" class="font-weight-bold text-primary">' . ($query->po_no ?? ('PO-' . str_pad($query->id, 5, '0', STR_PAD_LEFT))) . '</a>';
            })
            ->editColumn('vendor_name', function ($query) {
                return $query->vendor ? $query->vendor->shop_name : 'N/A';
            })
            ->editColumn('purchase_type', function ($query) {
                $type = $query->purchase_type ?? 'local';
                $badge = $type === 'foreign' ? 'badge-info' : 'badge-light border';
                return '<span class="badge ' . $badge . '">' . strtoupper($type) . '</span>';
            })
            ->editColumn('total_amount', function ($query) {
                if ($query->purchase_type === 'foreign' && $query->foreign_amount > 0) {
                    $symbol = $query->currency ? $query->currency->symbol : '$';
                    return '<span title="Base: kr. ' . number_format($query->total_amount, 2) . '">' . $symbol . ' ' . number_format($query->foreign_amount, 2) . '</span>';
                }
                $symbol = $query->currency ? $query->currency->symbol : 'kr.';
                return $symbol . ' ' . number_format($query->total_amount, 2);
            })
            ->editColumn('approval_status', function ($query) {
                $status = $query->approval_status ?? 'approved';
                $class = match ($status) {
                    'approved' => 'badge-success',
                    'pending' => 'badge-warning',
                    'level1_approved' => 'badge-info',
                    'rejected' => 'badge-danger',
                    default => 'badge-secondary',
                };
                return '<span class="badge ' . $class . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
            })
            ->editColumn('milestone_status', function ($query) {
                $milestone = $query->milestone_status ?? 'draft';
                $class = match ($milestone) {
                    'approved' => 'badge-success',
                    'po_sent' => 'badge-info',
                    'pi_attached' => 'badge-primary',
                    'lc_opened' => 'badge-purple',
                    'cancelled' => 'badge-danger',
                    default => 'badge-light border',
                };
                return '<span class="badge ' . $class . '">' . ucfirst(str_replace('_', ' ', $milestone)) . '</span>';
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at->format('d M, Y');
            })
            ->addColumn('action', function ($query) {
                $btn = '<a href="' . route('admin.purchase-orders.show', $query->id) . '" class="btn btn-info btn-sm mr-1" title="View PO Details"><i class="fas fa-eye"></i></a>';
                $btn .= '<a href="' . route('admin.vendor-bills.create', ['purchase_id' => $query->id]) . '" class="btn btn-success btn-sm mr-1" title="Generate Vendor Bill"><i class="fas fa-file-invoice-dollar"></i></a>';
                $btn .= '<a href="' . route('admin.purchase-orders.pdf.view', $query->id) . '" target="_blank" class="btn btn-secondary btn-sm mr-1" title="Preview PDF"><i class="fas fa-file-pdf"></i></a>';
                $btn .= '<a href="' . route('admin.purchase-orders.pdf.download', $query->id) . '" class="btn btn-danger btn-sm" title="Download PDF"><i class="fas fa-file-download"></i></a>';
                return $btn;
            })
            ->rawColumns(['po_no', 'purchase_type', 'total_amount', 'approval_status', 'milestone_status', 'action'])
            ->setRowId('id');
    }

    public function query(Purchase $model): QueryBuilder
    {
        return $model->newQuery()->with(['vendor', 'currency', 'comparisonStatement'])->whereNotNull('po_no')->orWhereNotNull('comparison_statement_id')->orderByDesc('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('po-table')
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

    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::computed('po_no')->title('PO Number'),
            Column::computed('vendor_name')->title('Supplier / Vendor'),
            Column::computed('purchase_type')->title('Type'),
            Column::make('total_amount')->title('Total Value'),
            Column::computed('approval_status')->title('Approval Status'),
            Column::computed('milestone_status')->title('Milestone Tracker'),
            Column::make('created_at')->title('Date'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width(120)->addClass('text-center'),
        ];
    }
}
