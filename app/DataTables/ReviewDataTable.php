<?php

namespace App\DataTables;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ReviewDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<Review> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('product', function ($row) {
                $product = $row->product;
                if (!$product) {
                    return '<span class="badge badge-light text-muted italic">Product Deleted</span>';
                }

                $fallbackSvg = asset('uploads/no-image.svg');
                $imgUrl = $fallbackSvg;

                if (!empty($product->thumb_image)) {
                    if (str_starts_with($product->thumb_image, 'http')) {
                        $imgUrl = $product->thumb_image;
                    } elseif (file_exists(public_path($product->thumb_image))) {
                        $imgUrl = asset($product->thumb_image);
                    }
                }

                $sku = e($product->sku ?? $product->product_number ?? 'N/A');
                $name = e($product->name);
                $categoryName = e($product->category?->name ?? '');
                $productUrl = route('product.details', $product->slug);

                $categoryBadge = $categoryName
                    ? '<span class="badge badge-light border text-primary" style="font-size: 10px; font-weight: 600; background: #eff6ff; border-color: #bfdbfe !important;">' . $categoryName . '</span>'
                    : '';

                return '
                    <div class="d-flex align-items-center" style="min-width: 230px;">
                        <div class="mr-2.5 flex-shrink-0">
                            <img src="' . $imgUrl . '" onerror="this.onerror=null;this.src=\'' . $fallbackSvg . '\';" alt="' . $name . '" class="rounded border" style="width: 46px; height: 46px; object-fit: contain; background: #ffffff; padding: 2px;">
                        </div>
                        <div style="min-width: 0; flex: 1;">
                            <a href="' . $productUrl . '" target="_blank" class="font-weight-bold text-dark d-block text-truncate" style="max-width: 190px; font-size: 13px; line-height: 1.3;" title="' . $name . '">
                                ' . $name . '
                            </a>
                            <div class="mt-1 d-flex flex-wrap align-items-center" style="gap: 4px;">
                                <span class="badge badge-light border text-muted" style="font-size: 10px; font-family: monospace;">SKU: ' . $sku . '</span>
                                ' . $categoryBadge . '
                            </div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('customer', function ($row) {
                $user = $row->user;
                if (!$user) {
                    return '<span class="text-muted italic">User Removed</span>';
                }

                $name = e($user->name);
                $outlet = e($user->outlet_name ?? 'Standard User');
                $email = e($user->email);

                return '
                    <div style="min-width: 170px;">
                        <div class="font-weight-bold text-dark" style="font-size: 13px;">' . $name . '</div>
                        <div class="small text-primary mt-0.5"><i class="fas fa-store mr-1"></i>' . $outlet . '</div>
                        <div class="small text-muted" style="font-size: 11px;">' . $email . '</div>
                    </div>
                ';
            })
            ->addColumn('rating_display', function ($row) {
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $color = $i <= $row->rating ? '#f59e0b' : '#e2e8f0';
                    $stars .= '<i class="fas fa-star" style="color: ' . $color . '; font-size: 11px; margin-right: 1px;"></i>';
                }
                return '
                    <div class="text-center" style="min-width: 100px;">
                        <div class="d-inline-block mb-1">
                            <span class="badge badge-warning text-dark font-weight-bold px-2 py-0.5" style="font-size: 11px; background-color: #fef3c7; color: #92400e !important; border: 1px solid #fde68a;">' . $row->rating . '.0 ★</span>
                        </div>
                        <div>' . $stars . '</div>
                    </div>
                ';
            })
            ->editColumn('comment', function ($row) {
                if (empty($row->comment)) {
                    return '<span class="text-muted italic" style="font-size: 12px; color: #94a3b8;">(No written comment)</span>';
                }
                $full = e($row->comment);
                $isLong = mb_strlen($row->comment) > 80;
                $short = e(\Illuminate\Support\Str::limit($row->comment, 80));

                return '
                    <div class="review-comment-wrap btn-view-review" data-id="' . $row->id . '" title="Click to view full review details" style="cursor: pointer;">
                        <span>' . $short . '</span>
                        ' . ($isLong ? '<span class="badge badge-light text-primary border ml-1 font-weight-bold" style="font-size: 10px; padding: 1px 4px;">more</span>' : '') . '
                    </div>
                ';
            })
            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : '';
                return '
                    <div class="d-flex justify-content-center align-items-center" style="min-width: 65px;">
                        <label class="custom-switch m-0" style="cursor: pointer;">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="' . $row->id . '" class="custom-switch-input toggle-review-status" ' . $checked . '>
                            <span class="custom-switch-indicator"></span>
                        </label>
                    </div>
                ';
            })
            ->editColumn('created_at', function ($row) {
                return '
                    <div class="text-center" style="min-width: 95px;">
                        <div class="font-weight-bold text-dark" style="font-size: 12px;">' . $row->created_at->format('Y-m-d') . '</div>
                        <div class="text-muted small" style="font-size: 11px;">' . $row->created_at->diffForHumans() . '</div>
                    </div>
                ';
            })
            ->addColumn('action', function ($row) {
                $viewBtn = '<button type="button" class="btn btn-sm btn-info mr-1 btn-view-review" data-id="' . $row->id . '" title="View Full Review"><i class="fas fa-eye"></i></button>';
                $deleteBtn = '<a href="' . route('admin.reviews.destroy', $row->id) . '" class="btn btn-sm btn-danger delete-item" title="Delete Review"><i class="fas fa-trash"></i></a>';
                return '<div class="btn-group" role="group">' . $viewBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['product', 'customer', 'rating_display', 'comment', 'status', 'created_at', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @param Review $model
     * @return QueryBuilder<Review>
     */
    public function query(Review $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['product.category:id,name', 'product:id,name,slug,sku,product_number,thumb_image,category_id', 'user:id,name,email,outlet_name,phone']);

        if ($this->request()->filled('category_id')) {
            $query->whereHas('product', function ($q) {
                $q->where('category_id', (int) $this->request()->get('category_id'));
            });
        }

        if ($this->request()->filled('rating')) {
            $query->where('rating', (int) $this->request()->get('rating'));
        }

        if ($this->request()->filled('status')) {
            $query->where('status', (int) $this->request()->get('status'));
        }

        if ($this->request()->filled('product_id')) {
            $query->where('product_id', (int) $this->request()->get('product_id'));
        }

        return $query->latest('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('reviews-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(5, 'desc')
            ->selectStyleSingle()
            ->parameters([
                'responsive' => false,
                'autoWidth' => false,
                'pageLength' => 10,
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                'dom' => "<'row mb-3 align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" .
                    "<'row'<'col-sm-12'tr>>" .
                    "<'row mt-3 align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('product')->title('Product')->width(230),
            Column::computed('customer')->title('Customer / Outlet')->width(180),
            Column::computed('rating_display')->title('Rating')->width(100)->addClass('text-center'),
            Column::make('comment')->title('Review Comment')->width(210),
            Column::computed('status')->title('Published')->width(80)->addClass('text-center'),
            Column::make('created_at')->title('Date')->width(105)->addClass('text-center'),
            Column::computed('action')->title('Action')
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Review_' . date('YmdHis');
    }
}
