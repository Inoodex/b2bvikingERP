<?php

namespace App\DataTables;

use App\Models\GeneralSetting;
use App\Models\User;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param mixed $query Results from query() method.
     */
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                $edit = '<a href="' . route('admin.users.edit', $query->id) . '" class="btn btn-primary"><i class="fas fa-edit"></i></a>';
                if ($query->id !== 1 && !$query->hasRole('Admin')) {
                    $delete = '<a href="' . route('admin.users.destroy', $query->id) . '" class="btn btn-danger delete-item ml-2"><i class="fas fa-trash"></i></a>';
                    return $edit . $delete;
                }
                return $edit;
            })
            ->addColumn('image', function ($query) {
                $url = $query->image ? asset($query->image) : 'https://ui-avatars.com/api/?name=' . urlencode($query->name);
                return '<img width="50px" height="50px" class="rounded-circle" src="' . $url . '" alt="">';
            })
            ->addColumn('role', function ($query) {
                return $query->userRole?->name ?? 'No Role';
            })
            ->addColumn('discount', function ($query) {
                if ($query->discount_type && $query->discount_value) {
                    $settings = GeneralSetting::first();
                    $type = $query->discount_type === 'flat' ? ($settings->currency_icon ?? 'kr') : '%';
                    
                    // If flat, show icon before value. If percent, show % after value.
                    $label = $query->discount_type === 'flat' 
                        ? $type . number_format($query->discount_value, 2)
                        : number_format($query->discount_value, 2) . $type;
                        
                    return '<span class="badge badge-info">' . $label . '</span>';
                }
                return '<span class="text-muted">No Discount</span>';
            })
            ->addColumn('customer_segment', function ($query) {
                $segment = $query->customer_segment ?? 'retail';
                $badgeMap = [
                    'retail' => 'badge-secondary',
                    'wholesale' => 'badge-info',
                    'b2b_vip' => 'badge-warning',
                    'distributor' => 'badge-primary',
                ];
                $class = $badgeMap[$segment] ?? 'badge-secondary';
                return '<span class="badge ' . $class . '">' . strtoupper(str_replace('_', ' ', $segment)) . '</span>';
            })
            ->addColumn('credit_limit', function ($query) {
                return 'kr. ' . number_format((float)($query->credit_limit ?? 0), 2);
            })
            ->addColumn('status', function ($query) {
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
            ->rawColumns(['image', 'action', 'customer_segment', 'credit_limit', 'discount', 'status'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model)
    {
        return $model->newQuery()->with('userRole');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
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
            Column::make('id'),
            Column::make('image'),
            Column::make('name'),
            Column::make('email'),
            Column::make('role'),
            Column::make('customer_segment')->title('Segment'),
            Column::make('credit_limit')->title('Credit Limit'),
            Column::make('discount'),
            Column::make('status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
