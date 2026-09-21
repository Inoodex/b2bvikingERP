<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Barcode\BarcodeLabelPreviewRequest;
use App\Http\Requests\Barcode\BarcodeLabelPrintRequest;
use App\Http\Requests\Barcode\BarcodeSearchRequest;
use App\Services\Barcode\BarcodeLabelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Enterprise Product & Variant Barcode Labels Orchestrator.
 * Follows Thin Controller Pattern: 100% domain logic delegated to BarcodeLabelService.
 */
class BarcodeLabelController extends Controller
{
    public function __construct(
        protected BarcodeLabelService $labelService
    ) {}

    /**
     * Display the Enterprise Product & Variant Barcode Labels Studio.
     */
    public function index(Request $request): View
    {
        $workspaceData = $this->labelService->getWorkspaceData($request->all());

        return view('backend.barcode_labels.index', $workspaceData);
    }

    /**
     * Fast AJAX Product and Variant search endpoint.
     */
    public function searchProducts(BarcodeSearchRequest $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $categoryId = $request->filled('category_id') ? $request->integer('category_id') : null;
        $limit = $request->integer('limit', 60);

        $results = $this->labelService->searchProducts($query, $categoryId, $limit);

        return response()->json(['data' => $results]);
    }

    /**
     * Generate and permanently save a unique barcode for a product or variant on demand.
     */
    public function generateBarcode(Request $request): JsonResponse
    {
        $targetType = (string) $request->input('target_type', 'product');
        $variantId = (int) $request->input('variant_id');
        $productId = (int) $request->input('product_id');

        if ($targetType === 'variant' || $variantId > 0) {
            $barcode = $this->labelService->generateBarcodeForVariant($variantId);
            $entityName = 'Product Variant';
            $resolvedId = $variantId;
            $type = 'variant';
        } else {
            $barcode = $this->labelService->generateBarcodeForProduct($productId);
            $entityName = 'Product';
            $resolvedId = $productId;
            $type = 'product';
        }

        if (! $barcode) {
            return response()->json([
                'success' => false,
                'message' => "{$entityName} not found.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'barcode' => $barcode,
            'target_type' => $type,
            'id' => $resolvedId,
            'message' => "Barcode generated and saved successfully for {$entityName}.",
        ]);
    }

    /**
     * Bulk generate and save barcodes for all queued items currently missing one.
     */
    public function generateAllMissing(Request $request): JsonResponse
    {
        $items = (array) $request->input('items', []);
        $updated = [];

        foreach ($items as $item) {
            $type = $item['target_type'] ?? 'product';
            $id = (int) ($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            if ($type === 'variant') {
                $barcode = $this->labelService->generateBarcodeForVariant($id);
                if ($barcode) {
                    $updated[] = [
                        'target_type' => 'variant',
                        'id' => $id,
                        'barcode' => $barcode,
                    ];
                }
            } else {
                $barcode = $this->labelService->generateBarcodeForProduct($id);
                if ($barcode) {
                    $updated[] = [
                        'target_type' => 'product',
                        'id' => $id,
                        'barcode' => $barcode,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'count' => count($updated),
            'items' => $updated,
            'message' => count($updated).' barcodes generated & saved successfully.',
        ]);
    }

    /**
     * Generate Live Preview HTML (AJAX).
     */
    public function preview(BarcodeLabelPreviewRequest $request): View
    {
        $presetKey = (string) $request->input('preset', 'thermal_50x30');
        $printMode = (string) $request->input('print_mode', 'single');

        $presets = $this->labelService->getPresets();
        $preset = $presets[$presetKey] ?? $presets['thermal_50x30'];

        $toggles = $this->labelService->parseToggles($request->validated());
        $items = (array) $request->input('items', []);

        $options = array_merge($toggles, [
            'print_mode' => $printMode,
        ]);

        $labels = $this->labelService->resolveProductLabels($items, $options, persistMissing: false);
        $unassignedCount = max(0, count($items) - $labels->count());

        return view('backend.barcode_labels.partials.preview_content', compact(
            'labels', 'preset', 'presetKey', 'toggles', 'printMode', 'unassignedCount'
        ));
    }

    /**
     * Clean Direct Print Page (Native Browser Print with Centered Layouts).
     */
    public function printView(BarcodeLabelPrintRequest $request): View
    {
        $presetKey = (string) $request->input('preset', 'thermal_50x30');
        $printMode = (string) $request->input('print_mode', 'single');

        $presets = $this->labelService->getPresets();
        $preset = $presets[$presetKey] ?? $presets['thermal_50x30'];

        $toggles = $this->labelService->parseToggles($request->validated());
        $items = $this->labelService->parseItems($request->input('items'));

        $options = array_merge($toggles, [
            'print_mode' => $printMode,
        ]);

        $labels = $this->labelService->resolveProductLabels($items, $options, persistMissing: false);

        return view('backend.barcode_labels.print', compact(
            'labels', 'preset', 'presetKey', 'toggles', 'printMode'
        ));
    }
}
