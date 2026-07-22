<?php

namespace App\DataTables;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BookingDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
            $edit = '<a href="' . route('admin.bookings.edit', $query->id) . '" class="btn btn-primary"><i class="fas fa-edit"></i></a>';
            $invoice = '<a href="' . route('admin.bookings.view-invoice', $query->id) . '" target="_blank" class="btn btn-warning ml-2" title="View Invoice"><i class="fas fa-file-invoice"></i></a>';
            $download = '<div class="btn-group ml-2">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-download"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="' . route('admin.bookings.download-pdf', $query->id) . '" class="dropdown-item">
                        <i class="fas fa-file-pdf text-danger"></i> PDF
                    </a>
                    <a href="' . route('admin.bookings.download-excel', $query->id) . '" class="dropdown-item">
                        <i class="fas fa-file-excel text-success"></i> Excel
                    </a>
                </div>
            </div>';
            $delete = '<a href="' . route('admin.bookings.destroy', $query->id) . '" class="btn btn-danger delete-item ml-2" data-booking-no="' . $query->booking_no . '"><i class="fas fa-trash"></i></a>';
            return $edit . $invoice . $download . $delete;
            })
            ->addColumn('vendor', function ($query) {
                return $query->vendor_shop_name ?? 'N/A';
            })
            ->addColumn('product_count', function ($query) {
                return '<span class="badge badge-info">' . $query->product_count . ' Items</span>';
            })
            ->addColumn('total_qty', function ($query) {
                return '<strong>' . $query->total_qty . '</strong>';
            })
            ->addColumn('status', function ($query) {
                $status = strtolower($query->status);
                $options = [
                    'pending' => 'Pending',
                    'complete' => 'Complete',
                    'cancelled' => 'Cancelled',
                    'missing' => 'Missing'
                ];
                
                $html = '<select class="form-control change-booking-status" data-id="' . $query->id . '" data-booking-no="' . $query->booking_no . '" style="min-width: 100px;">';
                foreach($options as $key => $label) {
                    $selected = $status === $key ? 'selected' : '';
                    $html .= '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
                }
                $html .= '</select>';
                
                return $html;
            })
            ->rawColumns(['action', 'status', 'product_count', 'total_qty'])
            ->setRowId('id');
    }

    public function query(Booking $model)
    {
        return $model->newQuery()
            ->join('vendors', 'bookings.vendor_id', '=', 'vendors.id')
            ->select(
                DB::raw('MIN(bookings.id) as id'),
                'bookings.booking_no',
                'bookings.vendor_id',
                'bookings.status',
                'bookings.shipping_method',
                'vendors.shop_name as vendor_shop_name',
                DB::raw('count(bookings.product_id) as product_count'),
                DB::raw('sum(bookings.qty) as total_qty')
            )
            ->groupBy('bookings.booking_no', 'bookings.vendor_id', 'bookings.status', 'bookings.shipping_method', 'vendors.shop_name');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('booking-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
            // Column::make('id'),
            Column::make('booking_no')->title('Booking No'),
            Column::make('vendor')->title('Vendor'),
            Column::computed('product_count')->title('Products')
                ->exportRender(function ($row, $value) {
                    $bookingNo = is_array($row) ? ($row['booking_no'] ?? '') : ($row->booking_no ?? '');
                    $bookings = \App\Models\Booking::where('booking_no', $bookingNo)
                        ->with('product')->get();
                    $items = [];
                    foreach ($bookings as $b) {
                        $name = $b->product?->name ?? 'Unknown';
                        if ($b->variant_info) {
                            $variants = [];
                            foreach ($b->variant_info as $vName => $vQty) {
                                $variants[] = "$vName: $vQty";
                            }
                            $name .= ' (' . implode(', ', $variants) . ')';
                        }
                        $items[] = $name;
                    }
                    return implode(', ', $items);
                }),
            Column::computed('total_qty')->title('Total Qty'),
            Column::make('shipping_method')->title('Shipping'),
            Column::make('status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(250)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Booking_' . date('YmdHis');
    }
}
