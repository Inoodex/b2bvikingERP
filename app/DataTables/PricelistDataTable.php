<?php

namespace App\DataTables;

use App\Models\Pricelist;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PricelistDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $editBtn = '<a href="' . route('admin.pricelists.edit', $query->id) . '" class="btn btn-sm btn-warning shadow-sm mr-1" title="Edit Pricelist"><i class="fas fa-edit"></i></a>';
                $deleteBtn = '<a href="' . route('admin.pricelists.destroy', $query->id) . '" class="btn btn-sm btn-danger shadow-sm delete-item" title="Delete"><i class="fas fa-trash"></i></a>';
                return $editBtn . $deleteBtn;
            })
            ->addColumn('customer_segment_badge', function ($query) {
                $segment = $query->customer_segment ?? 'retail';
                $badgeMap = [
                    'retail' => 'badge-secondary',
                    'wholesale' => 'badge-info',
                    'b2b_vip' => 'badge-warning',
                    'distributor' => 'badge-primary',
                ];
                $class = $badgeMap[$segment] ?? 'badge-secondary';
                return '<span class="badge ' . $class . ' px-3 py-1 font-weight-bold">' . strtoupper(str_replace('_', ' ', $segment)) . '</span>';
            })
            ->addColumn('validity_period', function ($query) {
                $from = $query->valid_from ? $query->valid_from->format('d M, Y') : 'Always';
                $to = $query->valid_to ? $query->valid_to->format('d M, Y') : 'Indefinite';
                return '<small class="font-weight-bold text-dark">' . $from . ' &rarr; ' . $to . '</small>';
            })
            ->addColumn('item_count', function ($query) {
                return '<span class="badge badge-light border font-weight-bold">' . $query->items_count . ' Products</span>';
            })
            ->addColumn('status', function ($query) {
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-pricelist-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->rawColumns(['action', 'customer_segment_badge', 'validity_period', 'item_count', 'status'])
            ->setRowId('id');
    }

    public function query(Pricelist $model)
    {
        return $model->newQuery()->withCount('items')->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('pricelist-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
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
            Column::make('id')->width(50),
            Column::make('name')->title('Pricelist Name'),
            Column::make('customer_segment_badge')->title('Customer Segment'),
            Column::make('validity_period')->title('Validity Period'),
            Column::make('item_count')->title('Tier Items'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Pricelists_' . date('YmdHis');
    }
}
