<?php

namespace App\DataTables;

use App\Models\Order;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RecycleBinDataTable extends DataTable
{
    protected string $itemType = 'product';

    public function setType(string $type): self
    {
        $this->itemType = $type;
        return $this;
    }

    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $type = $this->itemType;

        return (new EloquentDataTable($query))
            ->editColumn('name_or_title', function ($row) use ($type) {
                if ($type === 'product') {
                    return '<strong>' . e($row->name) . '</strong><br><small class="text-muted">SKU: ' . e($row->sku ?? 'N/A') . '</small>';
                } elseif ($type === 'order') {
                    return '<strong>' . e($row->order_no) . '</strong><br><small class="text-muted">' . e($row->billing_name) . '</small>';
                } elseif ($type === 'invoice') {
                    return '<strong>' . e($row->invoice_no) . '</strong><br><small class="text-muted">' . e($row->customer_name) . '</small>';
                } elseif ($type === 'vendor') {
                    return '<strong>' . e($row->shop_name) . '</strong><br><small class="text-muted">' . e($row->email) . '</small>';
                }
                return '<strong>' . e($row->name) . '</strong><br><small class="text-muted">' . e($row->email) . '</small>';
            })
            ->editColumn('deleted_at', function ($row) {
                return optional($row->deleted_at)->format('d M, Y h:i A');
            })
            ->addColumn('action', function ($row) use ($type) {
                $restoreUrl = route('admin.recycle-bin.restore', ['type' => $type, 'id' => $row->id]);
                $forceDeleteUrl = route('admin.recycle-bin.force-delete', ['type' => $type, 'id' => $row->id]);

                return '
                    <button type="button" class="btn btn-sm btn-success btn-restore-item mr-1" data-url="' . $restoreUrl . '" title="Restore Item">
                        <i class="fas fa-undo mr-1"></i> Restore
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-force-delete-item" data-url="' . $forceDeleteUrl . '" title="Permanently Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['name_or_title', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(): QueryBuilder
    {
        $type = request()->get('type', $this->itemType);
        $this->itemType = $type;

        switch ($type) {
            case 'order':
                return Order::onlyTrashed()->newQuery()->orderByDesc('deleted_at');
            case 'invoice':
                return SalesInvoice::onlyTrashed()->newQuery()->orderByDesc('deleted_at');
            case 'vendor':
                return Vendor::onlyTrashed()->newQuery()->orderByDesc('deleted_at');
            case 'customer':
                return User::onlyTrashed()->newQuery()->orderByDesc('deleted_at');
            case 'product':
            default:
                return Product::onlyTrashed()->newQuery()->orderByDesc('deleted_at');
        }
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('recyclebin-table')
            ->columns($this->getColumns())
            ->ajax([
                'url' => route('admin.recycle-bin.index'),
                'data' => 'function(d) { d.type = "' . $this->itemType . '"; }',
            ])
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
            Column::computed('name_or_title')->title('Record Identifier / Details'),
            Column::make('deleted_at')->title('Deleted On'),
            Column::computed('action')->title('Actions')->exportable(false)->printable(false)->width(180)->addClass('text-center'),
        ];
    }
}
