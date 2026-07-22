<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\ProductRequest;
use App\Support\PiInfoSupport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductRequestController extends Controller
{
    public function show(ProductRequest $productRequest)
    {
        abort_if((int) $productRequest->user_id !== (int) Auth::id(), 403);

        $productRequest->load([
            'user',
            'order',
            'items.product.category',
            'items.product.unit',
            'items.variant.color',
            'items.variant.size',
        ]);

        return view('frontend.pages.account.product-request-show', [
            'productRequest' => $productRequest,
        ]);
    }

    public function piInvoice(ProductRequest $productRequest)
    {
        abort_if((int) $productRequest->user_id !== (int) Auth::id(), 403);

        $productRequest->load([
            'user',
            'order',
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
        ]);

        $settings = GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($productRequest->pi_info, $productRequest->items, 'qty');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($productRequest->pi_info);

        $backUrl = $productRequest->order_id
            ? route('orders.show', $productRequest->order_id)
            : route('account.index', ['panel' => 'orders']);

        return view('backend.product-request.pi_invoice', [
            'productRequest' => $productRequest,
            'settings' => $settings,
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
            'hasSavedPiInfo' => $hasSavedPiInfo,
            'isFrontend' => true,
            'backUrl' => $backUrl,
            'downloadUrl' => route('product-requests.pi-invoice.download', $productRequest->id),
        ]);
    }

    public function downloadPiInvoice(ProductRequest $productRequest)
    {
        abort_if((int) $productRequest->user_id !== (int) Auth::id(), 403);

        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $path = 'invoices/pi-invoice-' . $productRequest->request_no . '.pdf';
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $productRequest->load([
            'user',
            'order',
            'items.product.category',
            'items.product.subCategory',
            'items.product.childCategory',
            'items.product.brand',
            'items.product.vendor',
            'items.product.unit',
            'items.product.productType',
            'items.variant.color',
            'items.variant.size',
        ]);

        $settings = GeneralSetting::first();
        $piInfo = PiInfoSupport::prepare($productRequest->pi_info, $productRequest->items, 'qty');
        $piTotals = PiInfoSupport::summarize($piInfo);
        $hasSavedPiInfo = PiInfoSupport::hasContent($productRequest->pi_info);

        $pdf = Pdf::setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif',
        ])->loadView('backend.product-request.pi_invoice', [
            'productRequest' => $productRequest,
            'settings' => $settings,
            'piInfo' => $piInfo,
            'piTotals' => $piTotals,
            'hasSavedPiInfo' => $hasSavedPiInfo,
            'isPdf' => true,
        ]);

        return $pdf->download('pi-invoice-' . $productRequest->request_no . '.pdf');
    }
}
