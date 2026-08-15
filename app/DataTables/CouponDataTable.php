<?php

namespace App\DataTables;

use App\Models\Coupon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CouponDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $editBtn = '<a href="' . route('admin.coupons.edit', $query->id) . '" class="btn btn-sm btn-warning shadow-sm mr-1" title="Edit Coupon"><i class="fas fa-edit"></i></a>';
                $deleteBtn = '<a href="' . route('admin.coupons.destroy', $query->id) . '" class="btn btn-sm btn-danger shadow-sm delete-item" title="Delete"><i class="fas fa-trash"></i></a>';
                return $editBtn . $deleteBtn;
            })
            ->addColumn('code_badge', function ($query) {
                return '<span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-family: monospace; font-size: 0.9rem; letter-spacing: 1px;"><i class="fas fa-ticket-alt mr-1"></i>' . e($query->code) . '</span>';
            })
            ->addColumn('discount_info', function ($query) {
                if ($query->discount) {
                    $type = $query->discount->discount_type === 'flat' ? 'kr.' : '%';
                    $val = number_format($query->discount->discount_value, 2);
                    $label = $query->discount->discount_type === 'flat' ? 'kr. ' . $val : $val . '%';
                    return '<span class="badge badge-info font-weight-bold">' . $label . ' OFF</span>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('usage_status', function ($query) {
                $limit = $query->usage_limit ?: '&infin;';
                return '<span class="badge badge-light border font-weight-bold">' . $query->used_count . ' / ' . $limit . ' Used</span>';
            })
            ->addColumn('expiry_date', function ($query) {
                if (!$query->expires_at) {
                    return '<span class="badge badge-success">No Expiry</span>';
                }
                $isExpired = $query->expires_at->isPast();
                $class = $isExpired ? 'badge-danger' : 'badge-light border text-dark';
                return '<span class="badge ' . $class . '">' . $query->expires_at->format('d M, Y H:i') . '</span>';
            })
            ->addColumn('status', function ($query) {
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-coupon-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->rawColumns(['action', 'code_badge', 'discount_info', 'usage_status', 'expiry_date', 'status'])
            ->setRowId('id');
    }

    public function query(Coupon $model)
    {
        return $model->newQuery()->with('discount')->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('coupon-table')
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
            Column::make('code_badge')->title('Coupon Code'),
            Column::make('discount_info')->title('Discount Rate'),
            Column::make('usage_status')->title('Usage Limit'),
            Column::make('expiry_date')->title('Expiration Date'),
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
        return 'Coupons_' . date('YmdHis');
    }
}
