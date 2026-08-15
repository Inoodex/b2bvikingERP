<?php

namespace App\DataTables;

use App\Models\SalesQuotation;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesQuotationDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<SalesQuotation> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('quotation_no_link', function ($query) {
                return '<a href="' . route('admin.sales-quotations.show', $query->id) . '" class="font-weight-bold text-primary" style="font-family: monospace; font-size: 0.95rem;">' . e($query->quotation_no) . '</a>';
            })
            ->addColumn('customer_name', function ($query) {
                return e($query->customer?->name ?? 'N/A');
            })
            ->addColumn('valid_until_badge', function ($query) {
                if (!$query->valid_until) {
                    return '<span class="badge badge-light text-muted">N/A</span>';
                }
                $isExpired = $query->valid_until->isPast();
                $badgeClass = $isExpired ? 'badge-danger' : 'badge-info';
                return '<span class="badge ' . $badgeClass . '">' . $query->valid_until->format('d M, Y') . '</span>';
            })
            ->addColumn('currency_code', function ($query) {
                return '<span class="badge badge-light border font-weight-bold">' . e($query->currency?->code ?? 'DKK') . '</span>';
            })
            ->addColumn('total_amount_formatted', function ($query) {
                $symbol = $query->currency?->symbol ?? 'kr.';
                return '<span class="font-weight-bold text-dark">' . $symbol . ' ' . number_format((float) $query->total_amount, 2) . '</span>';
            })
            ->addColumn('status_badge', function ($query) {
                switch ($query->status) {
                    case 'draft':
                        return '<span class="badge badge-secondary">Draft</span>';
                    case 'sent':
                        return '<span class="badge badge-info">Sent</span>';
                    case 'accepted':
                        return '<span class="badge badge-success">Accepted</span>';
                    case 'declined':
                        return '<span class="badge badge-danger">Declined</span>';
                    case 'converted':
                        return '<span class="badge badge-primary">Converted to SO</span>';
                    default:
                        return '<span class="badge badge-light">' . e(ucfirst($query->status)) . '</span>';
                }
            })
            ->addColumn('action', function ($query) {
                $viewBtn = '<a href="' . route('admin.sales-quotations.show', $query->id) . '" class="btn btn-sm btn-info shadow-sm mr-1" title="View"><i class="fas fa-eye"></i></a>';
                $editBtn = '';
                if ($query->status === 'draft') {
                    $editBtn = '<a href="' . route('admin.sales-quotations.edit', $query->id) . '" class="btn btn-sm btn-warning shadow-sm mr-1" title="Edit Quote"><i class="fas fa-edit"></i></a>';
                }
                $pdfBtn = '<a href="' . route('admin.sales-quotations.pdf', $query->id) . '" class="btn btn-sm btn-secondary shadow-sm mr-1" title="Download PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>';
                $cloneBtn = '<form action="' . route('admin.sales-quotations.clone', $query->id) . '" method="POST" class="d-inline mr-1">
                                ' . csrf_field() . '
                                <button type="submit" class="btn btn-sm btn-dark shadow-sm" title="Clone Quotation"><i class="fas fa-copy"></i></button>
                             </form>';
                $deleteBtn = '<a href="' . route('admin.sales-quotations.destroy', $query->id) . '" class="btn btn-sm btn-danger shadow-sm delete-item" title="Delete"><i class="fas fa-trash"></i></a>';

                return $viewBtn . $editBtn . $pdfBtn . $cloneBtn . $deleteBtn;
            })
            ->rawColumns([
                'quotation_no_link',
                'customer_name',
                'valid_until_badge',
                'currency_code',
                'total_amount_formatted',
                'status_badge',
                'action',
            ])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(SalesQuotation $model): QueryBuilder
    {
        return $model->newQuery()->with(['customer', 'currency'])->latest();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sales-quotation-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->selectStyleSingle();
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('quotation_no_link')->title('Quotation No'),
            Column::computed('customer_name')->title('Customer'),
            Column::computed('valid_until_badge')->title('Valid Until'),
            Column::computed('currency_code')->title('Currency'),
            Column::computed('total_amount_formatted')->title('Total Amount'),
            Column::computed('status_badge')->title('Status'),
            Column::computed('action')->title('Action')->addClass('text-right'),
        ];
    }
}
