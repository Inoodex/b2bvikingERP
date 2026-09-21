<?php

declare(strict_types=1);

namespace App\Services\Barcode;

class NativeBarcodeGenerator
{
    /**
     * Code 128 patterns for characters 0 through 106.
     * Each string represents the widths of alternating bars and spaces (e.g., '212222').
     */
    protected static array $patterns = [
        0 => '212222', 1 => '222122', 2 => '222221', 3 => '121223', 4 => '121322',
        5 => '131222', 6 => '122213', 7 => '122312', 8 => '132212', 9 => '221213',
        10 => '221312', 11 => '231212', 12 => '112232', 13 => '122132', 14 => '122231',
        15 => '113222', 16 => '123122', 17 => '123221', 18 => '223211', 19 => '221132',
        20 => '221231', 21 => '213212', 22 => '223112', 23 => '312131', 24 => '311222',
        25 => '321122', 26 => '321221', 27 => '312212', 28 => '322112', 29 => '322211',
        30 => '212123', 31 => '212321', 32 => '232121', 33 => '111323', 34 => '131123',
        35 => '131321', 36 => '112313', 37 => '132113', 38 => '132311', 39 => '211313',
        40 => '231113', 41 => '231311', 42 => '112133', 43 => '112331', 44 => '132131',
        45 => '113123', 46 => '113321', 47 => '133121', 48 => '313121', 49 => '211331',
        50 => '231131', 51 => '213113', 52 => '213311', 53 => '213131', 54 => '311123',
        55 => '311321', 56 => '331121', 57 => '312113', 58 => '312311', 59 => '332111',
        60 => '314111', 61 => '221411', 62 => '431111', 63 => '111224', 64 => '111422',
        65 => '121124', 66 => '121421', 67 => '141122', 68 => '141221', 69 => '112214',
        70 => '112412', 71 => '122114', 72 => '122411', 73 => '142112', 74 => '142211',
        75 => '241211', 76 => '221114', 77 => '413111', 78 => '241112', 79 => '134111',
        80 => '111242', 81 => '121142', 82 => '121241', 83 => '114212', 84 => '124112',
        85 => '124211', 86 => '411212', 87 => '421112', 88 => '421211', 89 => '212141',
        90 => '214121', 91 => '412121', 92 => '111143', 93 => '111341', 94 => '131141',
        95 => '114113', 96 => '114311', 97 => '411113', 98 => '411311', 99 => '113141',
        100 => '114131', 101 => '311141', 102 => '411131', 103 => '211412', 104 => '211214',
        105 => '211232', 106 => '2331112', // Stop character (includes 2-module termination bar)
    ];

    /**
     * Generate an inline, vector SVG barcode string for Code-128 (Subset B).
     *
     * @param  string  $code  Text/SKU to encode
     * @param  int  $height  Height of bars in pixels
     * @param  float  $barWidth  Narrowest bar module width in pixels
     * @param  int  $quietZone  Quiet zone width in pixels
     * @return string Inline SVG XML
     */
    public function getBarcodeSvg(string $code, int $height = 50, float $barWidth = 1.5, int $quietZone = 10): string
    {
        // 1. Sanitize input to ASCII printable characters
        $cleanCode = preg_replace('/[^\x20-\x7E]/', '', trim($code));
        if (empty($cleanCode)) {
            $cleanCode = '000000';
        }

        // 2. Start character for Code 128 Subset B is 104
        $startCode = 104;
        $checksum = $startCode;
        $encodedSymbols = [$startCode];

        $len = strlen($cleanCode);
        for ($i = 0; $i < $len; $i++) {
            $charVal = ord($cleanCode[$i]) - 32;
            $encodedSymbols[] = $charVal;
            $checksum += ($charVal * ($i + 1));
        }

        // 3. Append Checksum and Stop Character (106)
        $checksumSymbol = $checksum % 103;
        $encodedSymbols[] = $checksumSymbol;
        $encodedSymbols[] = 106; // Stop code

        // 4. Build bar modules sequence (1 for bar, 0 for space)
        $modules = [];
        foreach ($encodedSymbols as $symIndex) {
            $pattern = self::$patterns[$symIndex] ?? self::$patterns[0];
            $isBar = true;
            for ($p = 0; $p < strlen($pattern); $p++) {
                $width = (int) $pattern[$p];
                for ($w = 0; $w < $width; $w++) {
                    $modules[] = $isBar ? 1 : 0;
                }
                $isBar = ! $isBar;
            }
        }

        // 5. Generate SVG rectangles for bars
        $totalModules = count($modules);
        $totalWidth = ($totalModules * $barWidth) + ($quietZone * 2);

        $rects = '';
        $currentX = $quietZone;
        $barRun = 0;

        for ($m = 0; $m < $totalModules; $m++) {
            if ($modules[$m] === 1) {
                $barRun++;
            } else {
                if ($barRun > 0) {
                    $w = $barRun * $barWidth;
                    $x = $currentX;
                    $rects .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#000000"/>', $x, $w, $height);
                    $currentX += $w;
                    $barRun = 0;
                }
                $currentX += $barWidth;
            }
        }

        if ($barRun > 0) {
            $w = $barRun * $barWidth;
            $rects .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#000000"/>', $currentX, $w, $height);
        }

        return sprintf(
            '<svg viewBox="0 0 %.2f %d" width="100%%" height="100%%" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">%s</svg>',
            $totalWidth,
            $height,
            $rects
        );
    }
}
