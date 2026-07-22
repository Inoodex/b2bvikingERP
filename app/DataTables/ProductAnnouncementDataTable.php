<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductAnnouncementDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Product> $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('select', function (Product $product): string {
                return '<input type="checkbox" class="announcement-product-checkbox" data-id="' . (int) $product->id . '">';
            })
            ->addColumn('category_name', function (Product $product): string {
                return (string) ($product->category?->name ?? 'N/A');
            })
            ->addColumn('product_type_name', function (Product $product): string {
                if (!empty($product->productType?->name)) {
                    return (string) $product->productType->name;
                }

                return !empty($product->product_type) ? (string) $product->product_type : 'N/A';
            })
            ->addColumn('vendor_name', function (Product $product): string {
                return (string) ($product->vendor?->shop_name ?? 'N/A');
            })
            ->editColumn('stock_qty', function (Product $product): string {
                $stockQty = (float) ($product->stock_qty ?? 0);
                $badgeClass = $stockQty > 0 ? 'badge badge-info' : 'badge badge-danger';

                return '<span class="' . $badgeClass . '">' . rtrim(rtrim(number_format($stockQty, 2, '.', ''), '0'), '.') . '</span>';
            })
            ->addColumn('status_badge', function (Product $product): string {
                if ((int) $product->status === 1) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-secondary">Inactive</span>';
            })
            ->editColumn('updated_at', function (Product $product): string {
                return optional($product->updated_at)->format('d M Y h:i A') ?? '-';
            })
            ->rawColumns(['select', 'stock_qty', 'status_badge'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Product>
     */
    public function query(Product $model): QueryBuilder
    {
        $request = $this->request();
        $query = $model->newQuery()
            ->with(['category:id,name', 'productType:id,name', 'vendor:id,shop_name'])
            ->select([
                'id',
                'name',
                'product_number',
                'category_id',
                'product_type',
                'product_type_id',
                'vendor_id',
                'status',
                'updated_at',
            ])
            ->withSum('inventoryStocks as stock_qty', 'quantity');

        $categoryId = (int) $request->get('category');
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $productType = (string) $request->get('product_type', '');
        if ($productType !== '') {
            if (is_numeric($productType)) {
                $query->where('product_type_id', (int) $productType);
            } else {
                $query->where('product_type', $productType);
            }
        }

        $vendorId = (int) $request->get('vendor');
        if ($vendorId > 0) {
            $query->where('vendor_id', $vendorId);
        }

        $stockFilter = (string) $request->get('stock_filter', '');
        if ($stockFilter === 'in_stock') {
            $query->whereHas('inventoryStocks', function (QueryBuilder $q): void {
                $q->where('quantity', '>', 0);
            });
        } elseif ($stockFilter === 'new_stock') {
            $query->whereHas('inventoryStocks', function (QueryBuilder $q): void {
                $q->where('quantity', '>', 0)
                    ->where('updated_at', '>=', now()->subDays(7));
            });
        } elseif ($stockFilter === 'out_of_stock') {
            $query->whereDoesntHave('inventoryStocks', function (QueryBuilder $q): void {
                $q->where('quantity', '>', 0);
            });
        }

        $sort = (string) $request->get('sort', 'latest');
        if ($sort === 'a-z') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'z-a') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'active') {
            $query->where('status', 1)->latest();
        } elseif ($sort === 'inactive') {
            $query->where('status', 0)->latest();
        } else {
            $query->latest();
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-announcement-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(9, 'desc')
            ->parameters([
                'processing' => true,
                'serverSide' => true,
                'responsive' => true,
                'autoWidth' => false,
                'pageLength' => 25,
                'lengthMenu' => [[10, 25, 50, 100], [10, 25, 50, 100]],
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('select')
                ->title('<input type="checkbox" id="select-all-products">')
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-center')
                ->width(40),
            Column::make('id')->title('ID'),
            Column::make('name')->title('Product Name'),
            Column::make('product_number')->title('Code'),
            Column::computed('category_name')->title('Category')->orderable(false)->searchable(false),
            Column::computed('product_type_name')->title('Occasion/Type')->orderable(false)->searchable(false),
            Column::computed('vendor_name')->title('Vendor')->orderable(false)->searchable(false),
            Column::make('stock_qty')->title('Stock')->searchable(false)->addClass('text-center'),
            Column::computed('status_badge')->title('Status')->addClass('text-center')->orderable(false)->searchable(false),
            Column::make('updated_at')->title('Updated'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ProductAnnouncement_' . date('YmdHis');
    }
}
