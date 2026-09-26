<?php

declare(strict_types=1);

namespace App\Services\Barcode;

use App\Models\Category;

class Gs1TaxonomyResolver
{
    /**
     * Cached list of all bricks loaded from config.
     *
     * @var array<int, array<string, mixed>>|null
     */
    protected ?array $bricks = null;

    /**
     * Default fallback brick data.
     *
     * @var array<string, string>
     */
    protected array $defaultFallback;

    public function __construct()
    {
        $this->defaultFallback = config('gs1_gpc.default_fallback', [
            'code' => '10000000',
            'title' => 'General Merchandise / Unclassified Goods',
            'segment' => 'General Merchandise',
            'family' => 'General',
            'class' => 'General Goods',
        ]);
    }

    /**
     * Dynamically resolve the official 8-digit GS1 GPC Brick for any category or text query.
     *
     * @param string|Category|null $categoryOrName
     * @return array{
     *     code: string,
     *     title: string,
     *     segment: string,
     *     family: string,
     *     class: string,
     *     match_type: string,
     *     matched_keyword: ?string
     * }
     */
    public function resolve(string|Category|null $categoryOrName): array
    {
        // 1. Check if Category model passed with existing valid gpc_code
        if ($categoryOrName instanceof Category) {
            if (!empty($categoryOrName->gpc_code)) {
                $cleaned = preg_replace('/[^0-9]/', '', (string) $categoryOrName->gpc_code);
                if (!empty($cleaned)) {
                    $code = str_pad($cleaned, 8, '0', STR_PAD_LEFT);
                    $brick = $this->findByCode($code);
                    if ($brick) {
                        return array_merge($brick, [
                            'match_type' => 'database_assigned',
                            'matched_keyword' => null,
                        ]);
                    }

                    return [
                        'code' => $code,
                        'title' => $categoryOrName->gpc_title ?? 'Assigned GS1 GPC Category',
                        'segment' => 'General Merchandise',
                        'family' => 'Assigned',
                        'class' => 'Custom',
                        'match_type' => 'database_assigned',
                        'matched_keyword' => null,
                    ];
                }
            }

            $categoryOrName = $categoryOrName->name;
        }

        if (empty($categoryOrName) || !is_string($categoryOrName)) {
            return array_merge($this->defaultFallback, [
                'match_type' => 'fallback',
                'matched_keyword' => null,
            ]);
        }

        $inputRaw = trim($categoryOrName);
        $normalized = $this->normalizeString($inputRaw);

        if (empty($normalized)) {
            return array_merge($this->defaultFallback, [
                'match_type' => 'fallback',
                'matched_keyword' => null,
            ]);
        }

        $bricks = $this->getAllBricks();

        // 2. Exact match check against Brick Titles, Classes, or Families
        foreach ($bricks as $brick) {
            $brickTitle = $this->normalizeString($brick['title'] ?? '');
            if ($normalized === $brickTitle) {
                return array_merge($brick, [
                    'match_type' => 'exact_title',
                    'matched_keyword' => $brick['title'],
                ]);
            }
        }

        // 3. Multi-word phrase and keyword matching (sorted by keyword length descending)
        // Prioritizes longer, more specific phrases (e.g. 'rubber duck' over 'duck', 'tote bag' over 'bag')
        $allKeywords = [];
        foreach ($bricks as $brick) {
            foreach ($brick['keywords'] ?? [] as $kw) {
                $normKw = $this->normalizeString($kw);
                if (!empty($normKw)) {
                    $allKeywords[] = [
                        'keyword' => $normKw,
                        'length' => mb_strlen($normKw),
                        'brick' => $brick,
                    ];
                }
            }
        }

        usort($allKeywords, fn($a, $b) => $b['length'] <=> $a['length']);

        // Check exact match on keyword
        foreach ($allKeywords as $item) {
            if ($normalized === $item['keyword']) {
                return array_merge($item['brick'], [
                    'match_type' => 'exact_keyword',
                    'matched_keyword' => $item['keyword'],
                ]);
            }
        }

        // Check word-boundary or contained phrase match with optional plural suffix (s/es)
        foreach ($allKeywords as $item) {
            $kw = preg_quote($item['keyword'], '/');
            if (preg_match('/\b' . $kw . '(?:s|es)?\b/i', $normalized)) {
                return array_merge($item['brick'], [
                    'match_type' => 'phrase_match',
                    'matched_keyword' => $item['keyword'],
                ]);
            }
        }

        // Substring match without word boundary for combined words (e.g. "tshirt" in "vikingtshirt")
        foreach ($allKeywords as $item) {
            if (mb_strlen($item['keyword']) >= 4 && str_contains($normalized, $item['keyword'])) {
                return array_merge($item['brick'], [
                    'match_type' => 'substring_match',
                    'matched_keyword' => $item['keyword'],
                ]);
            }
        }

        // 4. Token-level matching
        $tokens = array_filter(explode(' ', $normalized), fn($t) => mb_strlen($t) >= 3);
        foreach ($tokens as $token) {
            foreach ($allKeywords as $item) {
                if ($token === $item['keyword']) {
                    return array_merge($item['brick'], [
                        'match_type' => 'token_match',
                        'matched_keyword' => $token,
                    ]);
                }
            }
        }

        // 5. Fuzzy / Levenshtein matching for slight typos (e.g. "sweter", "hodi", "bootz")
        foreach ($tokens as $token) {
            $tokenLen = mb_strlen($token);
            if ($tokenLen >= 4) {
                foreach ($allKeywords as $item) {
                    $kwLen = $item['length'];
                    if (abs($tokenLen - $kwLen) <= 2) {
                        $dist = levenshtein($token, $item['keyword']);
                        if ($dist === 1 || ($dist === 2 && $tokenLen >= 6)) {
                            return array_merge($item['brick'], [
                                'match_type' => 'fuzzy_match',
                                'matched_keyword' => $item['keyword'],
                            ]);
                        }
                    }
                }
            }
        }

        // 6. Default Fallback
        return array_merge($this->defaultFallback, [
            'match_type' => 'fallback',
            'matched_keyword' => null,
        ]);
    }

    /**
     * Search taxonomy bricks by keyword or code for UI autocomplete.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $limit = 20): array
    {
        $normalized = $this->normalizeString($query);
        if (empty($normalized)) {
            return array_slice($this->getAllBricks(), 0, $limit);
        }

        $results = [];
        foreach ($this->getAllBricks() as $brick) {
            if (str_contains($brick['code'], $normalized)
                || str_contains($this->normalizeString($brick['title']), $normalized)
                || str_contains($this->normalizeString($brick['segment']), $normalized)
                || str_contains($this->normalizeString($brick['family']), $normalized)
                || str_contains($this->normalizeString($brick['class']), $normalized)) {
                $results[] = $brick;
                continue;
            }

            foreach ($brick['keywords'] ?? [] as $kw) {
                if (str_contains($this->normalizeString($kw), $normalized)) {
                    $results[] = $brick;
                    break;
                }
            }

            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    /**
     * Find a brick definition by its 8-digit GS1 code.
     */
    public function findByCode(string $code): ?array
    {
        $clean = str_pad(preg_replace('/[^0-9]/', '', $code) ?: '', 8, '0', STR_PAD_LEFT);
        foreach ($this->getAllBricks() as $brick) {
            if ($brick['code'] === $clean) {
                return $brick;
            }
        }

        return null;
    }

    /**
     * Get all configured bricks from config.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllBricks(): array
    {
        if ($this->bricks === null) {
            $this->bricks = config('gs1_gpc.bricks', []);
        }

        return $this->bricks;
    }

    /**
     * Normalize a string for taxonomy comparison.
     */
    protected function normalizeString(string $value): string
    {
        $clean = mb_strtolower(trim($value), 'UTF-8');
        // Replace punctuation, dashes, slashes, underscores with a single space
        $clean = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $clean);
        return trim(preg_replace('/\s+/', ' ', $clean));
    }
}
