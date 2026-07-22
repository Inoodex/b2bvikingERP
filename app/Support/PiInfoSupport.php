<?php

namespace App\Support;

use Illuminate\Support\Collection;

class PiInfoSupport
{
    public static function prepare(?array $stored, iterable $items, string $qtyAttribute): array
    {
        $stored = is_array($stored) ? $stored : [];
        $items = collect($items)->values();
        $orderQtyTotal = $items->sum(fn ($item) => max(0, (int) data_get($item, $qtyAttribute, 0)));
        $piType = ($stored['pi_type'] ?? 'simple') === 'advanced' ? 'advanced' : 'simple';

        $simpleRows = collect($stored['rows'] ?? [])
            ->map(fn ($row) => self::normalizeSimpleRow($row))
            ->filter(fn ($row) => self::simpleRowHasAnyValue($row))
            ->values()
            ->all();

        if (empty($simpleRows)) {
            $simpleRows[] = self::blankSimpleRow();
        }

        return [
            'pi_type' => $piType,
            'order_qty_total' => $orderQtyTotal,
            'shipment_qty' => max(0, (int) ($stored['shipment_qty'] ?? $orderQtyTotal)),
            'shipment_date' => $stored['shipment_date'] ?? now()->toDateString(),
            'packing_note' => trim((string) ($stored['packing_note'] ?? '')),
            'rows' => $simpleRows,
            'blocks' => self::prepareAdvancedBlocks($stored['blocks'] ?? [], $items),
        ];
    }

    public static function sanitizePayload(array $validated): array
    {
        $piType = ($validated['pi_type'] ?? 'simple') === 'advanced' ? 'advanced' : 'simple';

        $payload = [
            'pi_type' => $piType,
            'shipment_qty' => max(0, (int) ($validated['shipment_qty'] ?? 0)),
            'shipment_date' => $validated['shipment_date'] ?? now()->toDateString(),
            'packing_note' => self::nullableString($validated['packing_note'] ?? null),
            'rows' => [],
            'blocks' => [],
        ];

        if ($piType === 'advanced') {
            $payload['blocks'] = collect($validated['advanced_blocks'] ?? [])
                ->map(fn ($block) => self::normalizeAdvancedBlock($block))
                ->filter(fn ($block) => self::advancedBlockHasAnyValue($block))
                ->values()
                ->all();
        } else {
            $payload['rows'] = collect($validated['pi_rows'] ?? [])
                ->map(fn ($row) => self::normalizeSimpleRow($row))
                ->filter(fn ($row) => self::simpleRowHasAnyValue($row))
                ->values()
                ->all();
        }

        return $payload;
    }

    public static function summarize(array $piInfo): array
    {
        $isAdvanced = ($piInfo['pi_type'] ?? 'simple') === 'advanced';
        $rows = $isAdvanced
            ? collect($piInfo['blocks'] ?? [])->flatMap(fn ($block) => $block['rows'] ?? [])->values()->all()
            : ($piInfo['rows'] ?? []);

        return collect($rows)->reduce(function (array $carry, array $row) {
            $rowPcs = self::rowPcs($row);
            $carry['ordered_qty'] += max(0, (int) ($row['ordered_qty'] ?? 0));
            $carry['ctn_qty'] += max(0, (int) ($row['ctn_qty'] ?? 0));
            $carry['total_pcs'] += max(0, (int) ($row['total_pcs'] ?? $rowPcs));
            $carry['nw_kg'] += max(0, (float) ($row['nw_kg'] ?? 0));
            $carry['gw_kg'] += max(0, (float) ($row['gw_kg'] ?? 0));
            return $carry;
        }, [
            'ordered_qty' => $isAdvanced ? max(0, (int) ($piInfo['order_qty_total'] ?? 0)) : 0,
            'ctn_qty' => 0,
            'total_pcs' => 0,
            'nw_kg' => 0.0,
            'gw_kg' => 0.0,
        ]);
    }

    public static function hasContent(?array $stored): bool
    {
        if (!is_array($stored)) {
            return false;
        }

        if (!empty($stored['packing_note']) || !empty($stored['shipment_qty']) || !empty($stored['shipment_date'])) {
            return true;
        }

        if (($stored['pi_type'] ?? 'simple') === 'advanced') {
            return collect($stored['blocks'] ?? [])->contains(fn ($block) => self::advancedBlockHasAnyValue((array) $block));
        }

        return collect($stored['rows'] ?? [])->contains(fn ($row) => self::simpleRowHasAnyValue((array) $row));
    }

    public static function rowPcs(array $row): int
    {
        if (array_key_exists('pcs', $row) && $row['pcs'] !== null) {
            return max(0, (int) $row['pcs']);
        }

        $variantTotal = collect($row['variants'] ?? [])->sum(fn ($value) => max(0, (int) $value));
        if ($variantTotal > 0) {
            return $variantTotal;
        }

        $sizeTotal = collect($row['sizes'] ?? [])->sum(fn ($value) => max(0, (int) $value));
        if ($sizeTotal > 0) {
            return $sizeTotal;
        }

        return collect($row['colors'] ?? [])->sum(fn ($value) => max(0, (int) $value));
    }

    private static function prepareAdvancedBlocks(array $storedBlocks, Collection $items): array
    {
        $normalizedStoredBlocks = collect($storedBlocks)
            ->map(fn ($block) => self::normalizeAdvancedBlock($block))
            ->values();

        $storedMap = $normalizedStoredBlocks
            ->filter(fn ($block) => !empty($block['block_key']))
            ->mapWithKeys(fn ($block) => [$block['block_key'] => $block])
            ->all();

        $derivedBlocks = self::deriveBlocksFromItems($items);

        return $derivedBlocks->map(function (array $derivedBlock) use ($storedMap, $normalizedStoredBlocks) {
            $storedBlock = $storedMap[$derivedBlock['block_key']] ?? null;
            if (!$storedBlock && !empty($derivedBlock['product_id'])) {
                $storedBlock = $normalizedStoredBlocks->first(fn ($block) => (int) ($block['product_id'] ?? 0) === (int) $derivedBlock['product_id']);
            }
            $ctnSize = self::nullableString($storedBlock['ctn_size'] ?? null);
            $colorHeaders = $storedBlock['color_headers'] ?? [];
            $colorHeaders = array_values(array_filter($colorHeaders, fn ($header) => trim((string) $header) !== ''));
            if (empty($colorHeaders)) {
                $colorHeaders = $derivedBlock['color_headers'];
            }
            $headers = $storedBlock['size_headers'] ?? [];
            $headers = array_values(array_filter($headers, fn ($header) => trim((string) $header) !== ''));
            if (empty($headers)) {
                $headers = $derivedBlock['size_headers'];
            }
            $variantHeaders = $storedBlock['variant_headers'] ?? [];
            $variantHeaders = array_values(array_filter($variantHeaders, fn ($header) => trim((string) $header) !== ''));
            if (empty($variantHeaders)) {
                $variantHeaders = $derivedBlock['variant_headers'];
            }
            if (!empty($derivedBlock['size_headers'])) {
                $derivedSizeLookup = array_map(fn ($header) => strtolower(trim((string) $header)), $derivedBlock['size_headers']);
                $colorLookup = array_map(fn ($header) => strtolower(trim((string) $header)), $colorHeaders);
                $hasSuspiciousOverlap = collect($headers)->contains(function ($header) use ($derivedSizeLookup, $colorLookup) {
                    $normalized = strtolower(trim((string) $header));
                    return in_array($normalized, $colorLookup, true) && !in_array($normalized, $derivedSizeLookup, true);
                });

                if ($hasSuspiciousOverlap) {
                    $headers = $derivedBlock['size_headers'];
                }
            }
            $rowCount = max(1, count($storedBlock['rows'] ?? []));
            $rows = [];
            for ($i = 0; $i < $rowCount; $i++) {
                $rows[] = self::prepareAdvancedRow($storedBlock['rows'][$i] ?? [], count($variantHeaders), count($colorHeaders), count($headers));
            }

            return [
                'block_key' => $derivedBlock['block_key'],
                'product_id' => $derivedBlock['product_id'],
                'title' => self::pickPreferredText($storedBlock['title'] ?? null, $derivedBlock['title'] ?? null),
                'color_label' => self::pickPreferredText($storedBlock['color_label'] ?? null, $derivedBlock['color_label'] ?? null, ['N/A']),
                'ctn_size' => $ctnSize,
                'image' => self::pickPreferredText($storedBlock['image'] ?? null, $derivedBlock['image'] ?? null),
                'variant_headers' => $variantHeaders,
                'variant_headers_csv' => implode(', ', $variantHeaders),
                'color_headers' => $colorHeaders,
                'color_headers_csv' => implode(', ', $colorHeaders),
                'size_headers' => $headers,
                'size_headers_csv' => implode(', ', $headers),
                'rows' => $rows,
            ];
        })->values()->all();
    }

    private static function deriveBlocksFromItems(Collection $items): Collection
    {
        $fallbackIndex = 0;

        return $items->groupBy(function ($item) use (&$fallbackIndex) {
            $productId = (int) ($item->product_id ?? 0);
            if ($productId > 0) {
                return 'product_' . $productId;
            }

            $fallbackIndex++;
            return 'line_' . $fallbackIndex;
        })->map(function (Collection $group, string $blockKey) {
            $first = $group->first();
            $colors = $group->map(fn ($item) => self::resolveItemColor($item))
                ->filter()
                ->unique()
                ->values();
            $sizes = $group->map(fn ($item) => self::resolveItemSize($item))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $variantHeaders = $group->map(fn ($item) => self::resolveItemVariantHeader($item))
                ->filter()
                ->unique()
                ->values()
                ->all();

            return [
                'block_key' => $blockKey,
                'product_id' => (int) ($first->product_id ?? 0),
                'title' => $first->product_name ?? ($first->product->name ?? 'Product'),
                'color_label' => $colors->count() === 1 ? $colors->first() : ($colors->count() > 1 ? implode(', ', $colors->all()) : 'N/A'),
                'image' => $first->product_image ?? ($first->product->thumb_image ?? null),
                'variant_headers' => $variantHeaders,
                'color_headers' => $colors->all(),
                'size_headers' => $sizes,
            ];
        });
    }

    private static function resolveItemColor(mixed $item): ?string
    {
        $variant = $item->variant ?? null;
        $colorName = trim((string) data_get($variant, 'color.name', ''));
        if ($colorName === '' && is_object($variant) && method_exists($variant, 'getAttribute')) {
            $colorName = trim((string) $variant->getAttribute('color'));
        }
        if ($colorName !== '') {
            return $colorName;
        }

        [$color, ] = self::extractVariantParts((string) ($item->variant_label ?? ''), null, null);
        return $color;
    }

    private static function resolveItemSize(mixed $item): ?string
    {
        $variant = $item->variant ?? null;
        $sizeName = trim((string) data_get($variant, 'size.name', ''));
        if ($sizeName === '' && is_object($variant) && method_exists($variant, 'getAttribute')) {
            $sizeName = trim((string) $variant->getAttribute('size'));
        }
        if ($sizeName !== '') {
            return $sizeName;
        }

        $knownColor = self::resolveItemColor($item);
        [, $size] = self::extractVariantParts((string) ($item->variant_label ?? ''), $knownColor, null);
        return $size;
    }

    private static function resolveItemVariantHeader(mixed $item): ?string
    {
        $color = self::resolveItemColor($item);
        $size = self::resolveItemSize($item);

        if ($color !== null && $size !== null) {
            return trim($color . ' ' . $size);
        }

        if ($color !== null) {
            return $color;
        }

        if ($size !== null) {
            return $size;
        }

        return self::nullableString((string) ($item->variant_label ?? ''));
    }

    private static function extractVariantParts(string $variantLabel, ?string $knownColor = null, ?string $knownSize = null): array
    {
        $label = trim($variantLabel);
        if ($label === '') {
            return [$knownColor, $knownSize];
        }

        $color = self::nullableString($knownColor);
        $size = self::nullableString($knownSize);

        $parts = preg_split('/\s*[-\/|,]\s*/', $label, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn ($part) => $part !== ''));

        if (count($parts) >= 2) {
            $candidateColor = implode(' ', array_slice($parts, 0, -1));
            $candidateSize = $parts[count($parts) - 1];

            $color = $color ?? self::nullableString($candidateColor);
            $size = $size ?? self::nullableString($candidateSize);
            return [$color, $size];
        }

        $tokens = preg_split('/\s+/', $label, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_filter(array_map('trim', $tokens), fn ($part) => $part !== ''));

        if (count($tokens) >= 2) {
            $lastToken = $tokens[count($tokens) - 1];
            if (self::looksLikeSizeToken($lastToken)) {
                $color = $color ?? self::nullableString(implode(' ', array_slice($tokens, 0, -1)));
                $size = $size ?? self::nullableString($lastToken);
                return [$color, $size];
            }
        }

        if ($color === null) {
            $color = self::nullableString($label);
        }

        return [$color, $size];
    }

    private static function looksLikeSizeToken(string $token): bool
    {
        $token = trim($token);
        if ($token === '') {
            return false;
        }

        return (bool) preg_match('/^(xxxl|xxl|xl|xs|s|m|l|free|small|medium|large|\d+(?:\/\d+)?[a-z]*)$/i', $token);
    }

    private static function pickPreferredText(?string $primary, ?string $fallback, array $placeholders = []): ?string
    {
        $primary = self::nullableString($primary);
        $fallback = self::nullableString($fallback);
        $normalizedPlaceholders = array_map(fn ($value) => strtolower(trim((string) $value)), $placeholders);

        if ($primary !== null && !in_array(strtolower($primary), $normalizedPlaceholders, true)) {
            return $primary;
        }

        return $fallback;
    }

    private static function prepareAdvancedRow(array $row, int $variantCount, int $colorCount, int $sizeCount): array
    {
        $normalized = self::normalizeAdvancedRow($row, $variantCount, $colorCount, $sizeCount);
        while (count($normalized['variants']) < $variantCount) {
            $normalized['variants'][] = null;
        }
        while (count($normalized['colors']) < $colorCount) {
            $normalized['colors'][] = null;
        }
        while (count($normalized['sizes']) < $sizeCount) {
            $normalized['sizes'][] = null;
        }

        return $normalized;
    }

    private static function normalizeAdvancedBlock(array $block): array
    {
        $variantHeaders = $block['variant_headers'] ?? null;
        if (!is_array($variantHeaders) || empty($variantHeaders)) {
            $variantHeaders = preg_split('/\s*,\s*/', (string) ($block['variant_headers_csv'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $variantHeaders = array_values(array_filter(array_map(
            fn ($header) => trim((string) $header),
            $variantHeaders
        )));

        $colorHeaders = $block['color_headers'] ?? null;
        if (!is_array($colorHeaders) || empty($colorHeaders)) {
            $colorHeaders = preg_split('/\s*,\s*/', (string) ($block['color_headers_csv'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $colorHeaders = array_values(array_filter(array_map(
            fn ($header) => trim((string) $header),
            $colorHeaders
        )));

        $headers = $block['size_headers'] ?? null;
        if (!is_array($headers) || empty($headers)) {
            $headers = preg_split('/\s*,\s*/', (string) ($block['size_headers_csv'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $headers = array_values(array_filter(array_map(
            fn ($header) => trim((string) $header),
            $headers
        )));

        $variantCount = count($variantHeaders);
        $colorCount = count($colorHeaders);
        $sizeCount = count($headers);
        $rows = collect($block['rows'] ?? [])
            ->map(fn ($row) => self::normalizeAdvancedRow((array) $row, $variantCount, $colorCount, $sizeCount))
            ->filter(fn ($row) => self::advancedRowHasAnyValue($row))
            ->values()
            ->all();

        $blockCtnSize = self::nullableString($block['ctn_size'] ?? null);
        if ($blockCtnSize === null) {
            $blockCtnSize = collect($block['rows'] ?? [])
                ->map(fn ($row) => self::nullableString($row['ctn_size'] ?? null))
                ->filter()
                ->first();
        }

        return [
            'block_key' => self::nullableString($block['block_key'] ?? null),
            'product_id' => self::nullableInt($block['product_id'] ?? null),
            'title' => self::nullableString($block['title'] ?? null),
            'color_label' => self::nullableString($block['color_label'] ?? null),
            'ctn_size' => $blockCtnSize,
            'image' => self::nullableString($block['image'] ?? null),
            'variant_headers' => $variantHeaders,
            'color_headers' => $colorHeaders,
            'size_headers' => $headers,
            'rows' => $rows,
        ];
    }

    private static function normalizeAdvancedRow(array $row, int $variantCount, int $colorCount, int $sizeCount): array
    {
        $variants = array_values(array_map(
            fn ($value) => self::nullableInt($value),
            is_array($row['variants'] ?? null) ? ($row['variants'] ?? []) : []
        ));

        while (count($variants) < $variantCount) {
            $variants[] = null;
        }

        $colors = array_values(array_map(
            fn ($value) => self::nullableInt($value),
            is_array($row['colors'] ?? null) ? ($row['colors'] ?? []) : []
        ));

        while (count($colors) < $colorCount) {
            $colors[] = null;
        }

        $sizes = array_values(array_map(
            fn ($value) => self::nullableInt($value),
            is_array($row['sizes'] ?? null) ? ($row['sizes'] ?? []) : []
        ));

        while (count($sizes) < $sizeCount) {
            $sizes[] = null;
        }

        return [
            'ctn_qty' => self::nullableInt($row['ctn_qty'] ?? null),
            'ctn_no' => self::nullableString($row['ctn_no'] ?? null),
            'variants' => $variantCount > 0 ? array_slice($variants, 0, $variantCount) : [],
            'colors' => $colorCount > 0 ? array_slice($colors, 0, $colorCount) : [],
            'sizes' => $sizeCount > 0 ? array_slice($sizes, 0, $sizeCount) : [],
            'pcs' => self::nullableInt($row['pcs'] ?? null),
            'total_pcs' => self::nullableInt($row['total_pcs'] ?? null),
            'nw_kg' => self::nullableFloat($row['nw_kg'] ?? null),
            'gw_kg' => self::nullableFloat($row['gw_kg'] ?? null),
        ];
    }

    private static function advancedBlockHasAnyValue(array $block): bool
    {
        return self::nullableString($block['title'] ?? null) !== null
            || self::nullableString($block['color_label'] ?? null) !== null
            || self::nullableString($block['ctn_size'] ?? null) !== null
            || !empty($block['variant_headers'] ?? [])
            || !empty($block['color_headers'] ?? [])
            || !empty($block['size_headers'] ?? [])
            || collect($block['rows'] ?? [])->contains(fn ($row) => self::advancedRowHasAnyValue((array) $row));
    }

    private static function advancedRowHasAnyValue(array $row): bool
    {
        return self::nullableInt($row['ctn_qty'] ?? null) !== null
            || self::nullableString($row['ctn_no'] ?? null) !== null
            || collect($row['variants'] ?? [])->contains(fn ($value) => self::nullableInt($value) !== null)
            || collect($row['colors'] ?? [])->contains(fn ($value) => self::nullableInt($value) !== null)
            || collect($row['sizes'] ?? [])->contains(fn ($value) => self::nullableInt($value) !== null)
            || self::nullableInt($row['pcs'] ?? null) !== null
            || self::nullableInt($row['total_pcs'] ?? null) !== null
            || self::nullableFloat($row['nw_kg'] ?? null) !== null
            || self::nullableFloat($row['gw_kg'] ?? null) !== null;
    }

    private static function blankSimpleRow(): array
    {
        return [
            'ordered_qty' => null,
            'ctn_no' => null,
            'ctn_size' => null,
            'pcs_per_ctn' => null,
            'ctn_qty' => null,
            'total_pcs' => null,
            'nw_kg' => null,
            'gw_kg' => null,
            'note' => null,
        ];
    }

    private static function normalizeSimpleRow(array $row): array
    {
        return [
            'ordered_qty' => self::nullableInt($row['ordered_qty'] ?? null),
            'ctn_no' => self::nullableString($row['ctn_no'] ?? null),
            'ctn_size' => self::nullableString($row['ctn_size'] ?? null),
            'pcs_per_ctn' => self::nullableInt($row['pcs_per_ctn'] ?? null),
            'ctn_qty' => self::nullableInt($row['ctn_qty'] ?? null),
            'total_pcs' => self::nullableInt($row['total_pcs'] ?? null),
            'nw_kg' => self::nullableFloat($row['nw_kg'] ?? null),
            'gw_kg' => self::nullableFloat($row['gw_kg'] ?? null),
            'note' => self::nullableString($row['note'] ?? null),
        ];
    }

    private static function simpleRowHasAnyValue(array $row): bool
    {
        return self::nullableInt($row['ordered_qty'] ?? null) !== null
            || self::nullableString($row['ctn_no'] ?? null) !== null
            || self::nullableString($row['ctn_size'] ?? null) !== null
            || self::nullableInt($row['pcs_per_ctn'] ?? null) !== null
            || self::nullableInt($row['ctn_qty'] ?? null) !== null
            || self::nullableInt($row['total_pcs'] ?? null) !== null
            || self::nullableFloat($row['nw_kg'] ?? null) !== null
            || self::nullableFloat($row['gw_kg'] ?? null) !== null
            || self::nullableString($row['note'] ?? null) !== null;
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (int) $value);
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (float) $value);
    }
}
