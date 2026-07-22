<?php

namespace App\DataTables;

use App\Models\CustomProductRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomProductRequestDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<CustomProductRequest> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('request_no', function (CustomProductRequest $request) {
                return '<span class="text-primary font-weight-bold">' . e($request->request_no) . '</span>';
            })
            ->addColumn('user_info', function (CustomProductRequest $request) {
                $name = trim((string) ($request->user?->name ?? ''));
                $outlet = $request->user?->outlet_name ?? 'No Outlet';
                $initial = $name !== '' ? strtoupper(mb_substr($name, 0, 1)) : 'U';

                return '<div class="user-info">'
                    . '<div class="user-avatar">' . e($initial) . '</div>'
                    . '<div>'
                    . '<div class="font-weight-bold">' . e($name !== '' ? $name : 'N/A') . '</div>'
                    . '<small class="text-muted">' . e($outlet) . '</small>'
                    . '</div>'
                    . '</div>';
            })
            ->addColumn('product_info', function (CustomProductRequest $request) {
                $exampleImages = $request->example_image ?? [];
                $imagePath = is_array($exampleImages) && !empty($exampleImages) ? $exampleImages[0] : null;

                if ($imagePath) {
                    $imageHtml = '<img src="' . e(asset($imagePath)) . '" width="80" height="80" alt="Product" class="request-image">';
                } else {
                    $imageHtml = '<div class="request-image d-flex align-items-center justify-content-center bg-light">'
                        . '<i class="fas fa-image text-muted"></i>'
                        . '</div>';
                }

                $productName = $request->product_name ?? 'N/A';
                $description = Str::limit((string) ($request->product_description ?? ''), 50);

                return '<div class="d-flex align-items-center gap-2">'
                    . $imageHtml
                    . '<div>'
                    . '<div class="font-weight-bold">' . e($productName) . '</div>'
                    . '<small class="text-muted text-truncate d-block" style="max-width: 150px;">' . e($description) . '</small>'
                    . '</div>'
                    . '</div>';
            })
            ->editColumn('status', function (CustomProductRequest $request) {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();

                if ($user && $user->can('Manage Custom Product Requests')) {
                    $pendingSelected = $request->status === 'pending' ? 'selected' : '';
                    $approvedSelected = $request->status === 'approved' ? 'selected' : '';
                    $rejectedSelected = $request->status === 'rejected' ? 'selected' : '';

                    return '<form action="' . e(route('admin.custom-product-requests.update-status', $request->id)) . '" method="POST" class="d-inline">'
                        . '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'
                        . '<input type="hidden" name="_method" value="PUT">'
                        . '<select name="status" class="form-control form-control-sm" onchange="this.form.submit()" style="width: auto; display: inline-block;">'
                        . '<option value="pending" ' . $pendingSelected . '>Pending</option>'
                        . '<option value="approved" ' . $approvedSelected . '>Approved</option>'
                        . '<option value="rejected" ' . $rejectedSelected . '>Rejected</option>'
                        . '</select>'
                        . '</form>';
                }

                $status = strtolower((string) $request->status);
                $class = match ($status) {
                    'pending' => 'badge-warning',
                    'approved' => 'badge-success',
                    'rejected' => 'badge-danger',
                    default => 'badge-secondary',
                };

                return '<span class="badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
            })
            ->editColumn('created_at', function (CustomProductRequest $request) {
                return '<div>' . e($request->created_at->format('d M Y')) . '</div>'
                    . '<small class="text-muted">' . e($request->created_at->format('h:i A')) . '</small>';
            })
            ->addColumn('action', function (CustomProductRequest $request) {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();

                $view = '<a href="' . e(route('admin.custom-product-requests.show', $request->id)) . '" class="btn btn-primary btn-sm mr-1" title="View Details">'
                    . '<i class="fas fa-eye"></i>'
                    . '</a>';

                $delete = '';
                if ($request->status === 'pending' || ($user && $user->can('Manage Custom Product Requests'))) {
                    $delete = '<a href="' . e(route('admin.custom-product-requests.destroy', $request->id)) . '" class="btn btn-danger btn-sm delete-item" title="Delete">'
                        . '<i class="fas fa-trash"></i>'
                        . '</a>';
                }

                return $view . $delete;
            })
            ->rawColumns(['request_no', 'user_info', 'product_info', 'status', 'created_at', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<CustomProductRequest>
     */
    public function query(CustomProductRequest $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['user']);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasRole('Admin')) {
            $query->where('user_id', $user?->id);
        }

        return $query->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('custom-product-request-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->stateSave(true)
            ->responsive(true)
            ->autoWidth(false)
            ->orderBy(0, 'desc');
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('request_no')->title('Request No.'),
            Column::computed('user_info')->title('Outlet/User'),
            Column::computed('product_info')->title('Product Info'),
            Column::make('quantity_needed')->title('Quantity'),
            Column::make('status')->title('Status'),
            Column::make('created_at')->title('Date'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(180)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Custom_Product_Requests_' . date('YmdHis');
    }
}
