<?php

namespace App\DataTables;

use App\Models\Product;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    public function dataTable($query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($query) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if (!$user->can('Manage Products')) {
                    return '';
                }
                 $edit = '<a href="' . route('admin.products.edit', $query->id) . '" class="btn btn-primary"><i class="fas fa-edit"></i></a>';
                 $delete = '<a href="' . route('admin.products.destroy', $query->id) . '" class="btn btn-danger delete-item ml-2"><i class="fas fa-trash"></i></a>';
                return $edit . $delete;
            })
            ->addColumn('thumb_image', function ($query) {
                 return $query->thumb_image ? '<img src="' . asset('storage/' . $query->thumb_image) . '" width="80px" class="img-thumbnail">' : '';
            })
            ->addColumn('status', function ($query) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if (!$user->can('Manage Products')) {
                    return $query->status ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                }
                $checked = $query->status ? 'checked' : '';
                return '<label class="custom-switch mt-2">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $query->id . '" class="custom-switch-input change-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>';
            })
             ->addColumn('category', function($query){
                return $query->category->name ?? '';
             })
             ->addColumn('price', function($query){
                return formatConverted($query->price);
             })
             ->addColumn('purchase_price', function($query){
                return formatConverted($query->purchase_price);
             })
             ->addColumn('outlet_price', function($query){
                return formatConverted($query->outlet_price);
             })
            ->editColumn('qty', function($query) {
                $stock = $query->inventory_stock;
                $badgeClass = $stock > 0 ? 'badge-info' : 'badge-danger';
                return '<span class="badge ' . $badgeClass . '">' . (float)$stock . '</span>';
            })
            ->rawColumns(['action', 'status', 'thumb_image', 'price', 'purchase_price', 'outlet_price', 'qty'])
            ->setRowId('id');
    }

    public function query(Product $model)
    {
        return $model->newQuery()->with(['category', 'inventoryStocks']); // Eager load category and stocks
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-table')
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
        $settings = getSettings();
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $columns = [
            // Column::make('id'),
            Column::make('thumb_image')->title('Image'),
            Column::make('name')->title('Product Name')->addClass('text-center'),
            Column::make('category')->title('Category Name')->addClass('text-center'),
        ];

        if ($user->can('Manage Purchases')) {
            $columns[] = Column::make('purchase_price')->title('Purchase Price')->addClass('text-center');
        }

        if ($user->hasRole('Outlet User') || $user->hasRole('User')) {
            $columns[] = Column::make('outlet_price')->title('Buying Price');
            $columns[] = Column::make('price')->title('Selling Price');
        } else {
            $columns[] = Column::make('price')->title('Selling Price');
            // Admin sees both for management
            if ($user->can('Manage Products')) {
                 $columns[] = Column::make('outlet_price')->title('Outlet/Shop Price');
            }
        }

        $columns[] = Column::make('qty')->title('Qty');

        if ($user->can('Manage Products')) {
            $columns[] = Column::make('status');
            $columns[] = Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center');
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Product_' . date('YmdHis');
    }
}
