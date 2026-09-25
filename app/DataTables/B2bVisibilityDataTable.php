<?php

namespace App\DataTables;

use App\Models\CustomerProductVisibility;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class B2bVisibilityDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('target_entity', function ($row) {
                if ($row->company) {
                    return '
                        <div class="d-flex align-items-center">
                            <div class="mr-2 text-primary" style="width: 32px; height: 32px; background: rgba(59, 130, 246, 0.12); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">
                                <i class="fas fa-building"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">' . e($row->company->name) . '</div>
                                <span class="badge badge-light border text-muted px-2 py-0" style="font-size: 10px;">Company Account</span>
                            </div>
                        </div>';
                } elseif ($row->outlet) {
                    $code = $row->outlet->code ? ' <span class="text-muted font-weight-normal">[' . e($row->outlet->code) . ']</span>' : '';
                    return '
                        <div class="d-flex align-items-center">
                            <div class="mr-2 text-info" style="width: 32px; height: 32px; background: rgba(6, 182, 212, 0.12); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">
                                <i class="fas fa-store"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">' . e($row->outlet->name) . $code . '</div>
                                <span class="badge badge-light border text-info px-2 py-0" style="font-size: 10px;">Retail Branch Outlet</span>
                            </div>
                        </div>';
                } elseif ($row->user) {
                    $phone = $row->user->phone ? '<div class="text-muted small" style="font-size: 11px;"><i class="fas fa-phone mr-1"></i>' . e($row->user->phone) . '</div>' : '';
                    return '
                        <div class="d-flex align-items-center">
                            <div class="mr-2 text-dark" style="width: 32px; height: 32px; background: rgba(15, 23, 42, 0.1); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">' . e($row->user->name) . '</div>
                                ' . $phone . '
                                <span class="badge badge-light border text-dark px-2 py-0" style="font-size: 10px;">Registered Buyer</span>
                            </div>
                        </div>';
                } elseif ($row->phone_number) {
                    return '
                        <div class="d-flex align-items-center">
                            <div class="mr-2 text-secondary" style="width: 32px; height: 32px; background: rgba(100, 116, 139, 0.12); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark" style="font-size: 13px;">' . e($row->phone_number) . '</div>
                                <span class="badge badge-light border text-secondary px-2 py-0" style="font-size: 10px;">Buyer Phone Override</span>
                            </div>
                        </div>';
                }

                return '<span class="text-muted font-italic">Global Customer</span>';
            })
            ->addColumn('product_info', function ($row) {
                if (!$row->product) {
                    return '<span class="text-danger font-italic">Product deleted or not found</span>';
                }

                $thumb = $row->product->thumb_image ? asset($row->product->thumb_image) : asset('uploads/no-image.svg');
                $editUrl = route('admin.products.edit', $row->product_id);
                $sku = $row->product->product_number ?? $row->product->sku ?? 'N/A';
                $category = $row->product->category ? e($row->product->category->name) : 'General';
                $stock = (float) ($row->product->inventory_stock ?? $row->product->inventoryStocks()->sum('quantity'));
                $stockBadge = $stock > 0 
                    ? '<span class="badge badge-success px-2 py-0 font-weight-bold" style="font-size: 10px;">' . number_format($stock) . ' In Stock</span>'
                    : '<span class="badge badge-danger px-2 py-0 font-weight-bold" style="font-size: 10px;">0 Stock (Actual)</span>';

                return '
                    <div class="d-flex align-items-center">
                        <img src="' . $thumb . '" alt="' . e($row->product->name) . '" class="rounded mr-2 shadow-sm border" style="width: 44px; height: 44px; object-fit: cover;">
                        <div style="min-width: 0;">
                            <a href="' . $editUrl . '" class="font-weight-bold text-dark text-truncate d-block" style="font-size: 13px; max-width: 260px;" title="' . e($row->product->name) . '">
                                ' . e($row->product->name) . '
                            </a>
                            <div class="text-muted small d-flex align-items-center flex-wrap" style="font-size: 11px; gap: 6px;">
                                <span><strong>SKU:</strong> ' . e($sku) . '</span>
                                <span>&bull;</span>
                                <span>' . $category . '</span>
                                <span>&bull;</span>
                                ' . $stockBadge . '
                            </div>
                        </div>
                    </div>';
            })
            ->addColumn('visibility_badge', function ($row) {
                if ($row->visibility_mode === 'force_in_stock') {
                    return '
                        <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 20px; font-size: 11.5px;">
                            <i class="fas fa-check-circle mr-1"></i> Priority Available (In-Stock)
                        </span>';
                } elseif ($row->visibility_mode === 'force_out_of_stock') {
                    return '
                        <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(239, 68, 68, 0.12); color: #dc2626; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 20px; font-size: 11.5px;">
                            <i class="fas fa-ban mr-1"></i> Restricted (Out of Stock)
                        </span>';
                } elseif ($row->visibility_mode === 'hide_product') {
                    return '
                        <span class="badge px-3 py-1 font-weight-bold" style="background: rgba(100, 116, 139, 0.15); color: #475569; border: 1px solid rgba(100, 116, 139, 0.3); border-radius: 20px; font-size: 11.5px;">
                            <i class="fas fa-eye-slash mr-1"></i> Catalog Exclusion (Hidden)
                        </span>';
                }

                return '<span class="badge badge-light border font-weight-bold">' . e($row->visibility_mode) . '</span>';
            })
            ->addColumn('notes_col', function ($row) {
                $notes = $row->notes ? e($row->notes) : '<span class="text-muted font-italic">—</span>';
                $creator = $row->creator ? '<small class="text-muted d-block mt-1" style="font-size: 10px;"><i class="fas fa-user-edit mr-1"></i>By ' . e($row->creator->name) . '</small>' : '';
                return '<div style="font-size: 12px; max-width: 220px;" class="text-truncate" title="' . e($row->notes ?? '') . '">' . $notes . $creator . '</div>';
            })
            ->addColumn('created_date', function ($row) {
                return '<span class="small text-muted font-weight-500">' . ($row->created_at ? $row->created_at->format('d M, Y') : '—') . '</span>';
            })
            ->addColumn('action', function ($row) {
                $targetType = $row->company_id ? 'company' : ($row->outlet_id ? 'outlet' : 'phone');
                $targetId = $row->company_id ?? $row->outlet_id ?? $row->user_id ?? '';
                
                $editData = htmlspecialchars(json_encode([
                    'id' => $row->id,
                    'product_id' => $row->product_id,
                    'product_name' => $row->product->name ?? '',
                    'target_type' => $targetType,
                    'company_id' => $row->company_id,
                    'outlet_id' => $row->outlet_id,
                    'user_id' => $row->user_id,
                    'phone_number' => $row->phone_number,
                    'visibility_mode' => $row->visibility_mode,
                    'notes' => $row->notes,
                ]), ENT_QUOTES, 'UTF-8');

                $editBtn = '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-visibility mr-1" data-rule=\'' . $editData . '\' title="Edit Rule" style="border-radius: 6px;"><i class="fas fa-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-rule" data-id="' . $row->id . '" title="Remove Override" style="border-radius: 6px;"><i class="fas fa-trash-alt"></i></button>';
                
                return '<div class="d-flex align-items-center justify-content-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['target_entity', 'product_info', 'visibility_badge', 'notes_col', 'created_date', 'action'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param CustomerProductVisibility $model
     * @return QueryBuilder
     */
    public function query(CustomerProductVisibility $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['product.category', 'company', 'outlet', 'user', 'creator'])
            ->latest('id');

        // Apply filters
        $req = request();

        if ($req->filled('company_id')) {
            $query->where('company_id', $req->get('company_id'));
        }

        if ($req->filled('outlet_id')) {
            $query->where('outlet_id', $req->get('outlet_id'));
        }

        if ($req->filled('visibility_mode')) {
            $query->where('visibility_mode', $req->get('visibility_mode'));
        }

        if ($req->filled('category_id')) {
            $query->whereHas('product', function ($p) use ($req) {
                $p->where('category_id', $req->get('category_id'));
            });
        }

        if ($req->filled('target_scope')) {
            $scope = $req->get('target_scope');
            if ($scope === 'company') {
                $query->whereNotNull('company_id');
            } elseif ($scope === 'outlet') {
                $query->whereNotNull('outlet_id');
            } elseif ($scope === 'buyer') {
                $query->where(function ($q) {
                    $q->whereNotNull('user_id')->orWhereNotNull('phone_number');
                });
            }
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('b2b-visibility-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->selectStyleSingle()
            ->pageLength(10)
            ->responsive(true)
            ->autoWidth(false)
            ->parameters([
                'lengthMenu' => [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ]
            ])
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#')->width('5%')->addClass('text-center align-middle font-weight-bold'),
            Column::computed('target_entity')->title('Target Account / Scope')->width('22%')->addClass('align-middle'),
            Column::computed('product_info')->title('Product Details & Real Stock')->width('28%')->addClass('align-middle'),
            Column::computed('visibility_badge')->title('Availability Policy')->width('18%')->addClass('align-middle'),
            Column::computed('notes_col')->title('Notes & Reason')->width('15%')->addClass('align-middle'),
            Column::computed('created_date')->title('Effective Date')->width('12%')->addClass('align-middle'),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->width('80px')->addClass('text-center align-middle'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'B2B_Customer_Stock_Rules_' . date('YmdHis');
    }
}
