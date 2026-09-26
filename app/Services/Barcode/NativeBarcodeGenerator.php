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

    /**
     * Generate an inline, vector SVG 2D QR Code.
     *
     * @param string $data URL or barcode payload for customer/smartphone scanning
     * @param int $size Width/height in pixels
     * @param int $margin Quiet zone around QR code
     * @return string Inline SVG XML string
     */
    public function getQrCodeSvg(string $data, int $size = 70, int $margin = 1): string
    {
        $cleanData = trim($data);
        if (empty($cleanData)) {
            $cleanData = 'https://b2bviking.com';
        }

        return $this->generatePureNativeQrSvg($cleanData, $margin);
    }

    /**
     * Pure Native PHP QR Code Model 2 SVG Generator (Zero External Composer Packages).
     */
    protected function generatePureNativeQrSvg(string $text, int $margin = 1): string
    {
        $len = strlen($text);
        if ($len <= 17) {
            $version = 1; $dataCap = 19; $ecLen = 7;
        } elseif ($len <= 32) {
            $version = 2; $dataCap = 34; $ecLen = 10;
        } elseif ($len <= 53) {
            $version = 3; $dataCap = 55; $ecLen = 15;
        } else {
            $version = 4; $dataCap = 80; $ecLen = 20;
        }

        $matrixSize = $version * 4 + 17;

        // Byte Mode encoding
        $bits = '0100'; // Byte mode
        $bits .= sprintf('%08b', $len);
        for ($i = 0; $i < $len; $i++) {
            $bits .= sprintf('%08b', ord($text[$i]));
        }

        // Terminator up to 4 bits
        $neededBits = $dataCap * 8;
        $termLen = min(4, $neededBits - strlen($bits));
        $bits .= str_repeat('0', max(0, $termLen));

        // Pad to byte
        if (strlen($bits) % 8 !== 0) {
            $bits .= str_repeat('0', 8 - (strlen($bits) % 8));
        }

        // Pad bytes
        $padBytes = [0xEC, 0x11];
        $padIdx = 0;
        while (strlen($bits) < $neededBits) {
            $bits .= sprintf('%08b', $padBytes[$padIdx]);
            $padIdx = 1 - $padIdx;
        }

        // Convert bits to data bytes
        $dataBytes = [];
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $dataBytes[] = bindec(substr($bits, $i, 8));
        }

        // Generate RS Error Correction
        $ecBytes = $this->rsEncode($dataBytes, $ecLen);
        $allCodewords = array_merge($dataBytes, $ecBytes);

        // Matrix initialization
        $matrix = array_fill(0, $matrixSize, array_fill(0, $matrixSize, null));
        $reserved = array_fill(0, $matrixSize, array_fill(0, $matrixSize, false));

        // Place Finder Patterns
        $setFinder = function($r, $c) use (&$matrix, &$reserved) {
            for ($i = -1; $i <= 7; $i++) {
                for ($j = -1; $j <= 7; $j++) {
                    $row = $r + $i; $col = $c + $j;
                    if ($row >= 0 && $row < count($matrix) && $col >= 0 && $col < count($matrix)) {
                        $isBlack = ($i >= 0 && $i <= 6 && ($j === 0 || $j === 6)) ||
                                   ($j >= 0 && $j <= 6 && ($i === 0 || $i === 6)) ||
                                   ($i >= 2 && $i <= 4 && $j >= 2 && $j <= 4);
                        $matrix[$row][$col] = $isBlack ? 1 : 0;
                        $reserved[$row][$col] = true;
                    }
                }
            }
        };

        $setFinder(0, 0);
        $setFinder(0, $matrixSize - 7);
        $setFinder($matrixSize - 7, 0);

        // Timing patterns
        for ($i = 8; $i < $matrixSize - 8; $i++) {
            $val = ($i % 2 === 0) ? 1 : 0;
            if (!$reserved[6][$i]) { $matrix[6][$i] = $val; $reserved[6][$i] = true; }
            if (!$reserved[$i][6]) { $matrix[$i][6] = $val; $reserved[$i][6] = true; }
        }

        // Alignment pattern for V2, V3, V4
        if ($version >= 2) {
            $alignPos = [
                2 => 18,
                3 => 22,
                4 => 26
            ][$version];
            for ($i = -2; $i <= 2; $i++) {
                for ($j = -2; $j <= 2; $j++) {
                    $row = $alignPos + $i; $col = $alignPos + $j;
                    $isBlack = (abs($i) === 2 || abs($j) === 2 || ($i === 0 && $j === 0));
                    $matrix[$row][$col] = $isBlack ? 1 : 0;
                    $reserved[$row][$col] = true;
                }
            }
        }

        // Dark module
        $matrix[$matrixSize - 8][8] = 1;
        $reserved[$matrixSize - 8][8] = true;

        // Reserve Format Information areas
        for ($i = 0; $i <= 8; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
            $reserved[8][$matrixSize - 1 - $i] = true;
            $reserved[$matrixSize - 1 - $i][8] = true;
        }

        // Place Data Codewords into matrix (snake path)
        $allBits = '';
        foreach ($allCodewords as $b) {
            $allBits .= sprintf('%08b', $b);
        }

        $bitIdx = 0;
        $totalBits = strlen($allBits);
        $col = $matrixSize - 1;
        $dir = -1; // -1 = up, 1 = down

        while ($col > 0) {
            if ($col === 6) $col--; // skip vertical timing line
            $row = ($dir === -1) ? $matrixSize - 1 : 0;
            while ($row >= 0 && $row < $matrixSize) {
                for ($c = 0; $c < 2; $c++) {
                    $currCol = $col - $c;
                    if (!$reserved[$row][$currCol]) {
                        $bit = ($bitIdx < $totalBits) ? (int)$allBits[$bitIdx++] : 0;
                        // Apply mask 0: (row + col) % 2 === 0
                        if (($row + $currCol) % 2 === 0) {
                            $bit ^= 1;
                        }
                        $matrix[$row][$currCol] = $bit;
                    }
                }
                $row += $dir;
            }
            $dir = -$dir;
            $col -= 2;
        }

        // Format Info (Level L, Mask 0 = 0b111011111000100)
        $formatBits = '111011111000100';
        $matrix[8][0] = (int)$formatBits[0];
        $matrix[8][1] = (int)$formatBits[1];
        $matrix[8][2] = (int)$formatBits[2];
        $matrix[8][3] = (int)$formatBits[3];
        $matrix[8][4] = (int)$formatBits[4];
        $matrix[8][5] = (int)$formatBits[5];
        $matrix[8][7] = (int)$formatBits[6];
        $matrix[8][8] = (int)$formatBits[7];
        $matrix[7][8] = (int)$formatBits[8];
        $matrix[5][8] = (int)$formatBits[9];
        $matrix[4][8] = (int)$formatBits[10];
        $matrix[3][8] = (int)$formatBits[11];
        $matrix[2][8] = (int)$formatBits[12];
        $matrix[1][8] = (int)$formatBits[13];
        $matrix[0][8] = (int)$formatBits[14];

        for ($i = 0; $i < 7; $i++) {
            $matrix[$matrixSize - 1 - $i][8] = (int)$formatBits[$i];
        }
        for ($i = 0; $i < 8; $i++) {
            $matrix[8][$matrixSize - 8 + $i] = (int)$formatBits[7 + $i];
        }

        // Fast SVG generation with crisp vector paths
        $viewSize = $matrixSize + ($margin * 2);
        $path = '';
        for ($r = 0; $r < $matrixSize; $r++) {
            for ($c = 0; $c < $matrixSize; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $path .= "M{$x},{$y}h1v1h-1z";
                }
            }
        }

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$viewSize} {$viewSize}\" width=\"100%\" height=\"100%\" shape-rendering=\"crispEdges\"><rect width=\"100%\" height=\"100%\" fill=\"#ffffff\"/><path d=\"{$path}\" fill=\"#000000\"/></svg>";
    }

    private static ?array $gfExp = null;
    private static ?array $gfLog = null;

    private function initGf(): void
    {
        if (self::$gfExp !== null) return;
        self::$gfExp = [];
        self::$gfLog = [];
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$gfExp[$i] = $x;
            self::$gfLog[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) $x ^= 0x11D;
        }
        for ($i = 255; $i < 512; $i++) {
            self::$gfExp[$i] = self::$gfExp[$i - 255];
        }
    }

    private function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) return 0;
        return self::$gfExp[self::$gfLog[$x] + self::$gfLog[$y]];
    }

    private function rsGenPoly(int $ecLen): array
    {
        $poly = [1];
        for ($i = 0; $i < $ecLen; $i++) {
            $root = self::$gfExp[$i];
            $temp = [];
            for ($j = 0; $j < count($poly); $j++) {
                $temp[$j + 1] = $poly[$j];
            }
            $temp[0] = 0;
            for ($j = 0; $j < count($poly); $j++) {
                $temp[$j] ^= $this->gfMul($poly[$j], $root);
            }
            $poly = $temp;
        }
        return $poly;
    }

    private function rsEncode(array $data, int $ecLen): array
    {
        $this->initGf();
        $poly = $this->rsGenPoly($ecLen);
        $res = array_fill(0, $ecLen, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $res[0];
            array_shift($res);
            $res[] = 0;
            for ($i = 0; $i < $ecLen; $i++) {
                $res[$i] ^= $this->gfMul($poly[$i], $factor);
            }
        }
        return $res;
    }
}
