<?php

namespace App\Support;

class PdfImageHelper
{
    /**
     * Cache for optimized images to avoid redundant processing within a single request.
     */
    protected static array $cache = [];

    /**
     * Optimize an image for PDF embedding.
     * Resizes, compresses, and returns a Base64 encoded string.
     *
     * @param string|null $path File path or URL
     * @param int $width Max width
     * @param int $height Max height
     * @param int $quality JPEG quality (0-100)
     * @return string|null Base64 data URI or null on failure
     */
    public static function optimize(?string $path, int $width = 80, int $height = 80, int $quality = 70): ?string
    {
        if (!$path) {
            return null;
        }

        // Check cache
        $cacheKey = md5($path . $width . $height . $quality);
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        try {
            $imageData = null;
            $type = null;

            // Handle remote URLs
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $imageData = @file_get_contents($path);
                if (!$imageData) {
                    return null;
                }
                $tempFile = tempnam(sys_get_temp_dir(), 'pdf_img_');
                file_put_contents($tempFile, $imageData);
                $info = @getimagesize($tempFile);
                if ($info) {
                    $type = $info[2];
                }
                @unlink($tempFile);
            } else {
                // Handle local paths
                $normalized = ltrim($path, '/');
                $candidates = [
                    public_path($normalized),
                    public_path('storage/' . ltrim(str_replace('storage/', '', $normalized), '/')),
                    storage_path('app/public/' . ltrim(str_replace('storage/', '', $normalized), '/')),
                ];

                foreach ($candidates as $candidate) {
                    if (is_file($candidate)) {
                        $path = $candidate;
                        $info = @getimagesize($path);
                        if ($info) {
                            $type = $info[2];
                            break;
                        }
                    }
                }
            }

            if (!$type) {
                return null;
            }

            $src = null;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $src = @imagecreatefromjpeg($path);
                    break;
                case IMAGETYPE_PNG:
                    $src = @imagecreatefrompng($path);
                    break;
                case IMAGETYPE_GIF:
                    $src = @imagecreatefromgif($path);
                    break;
                case 18: // IMAGETYPE_WEBP
                    if (function_exists('imagecreatefromwebp')) {
                        $src = @imagecreatefromwebp($path);
                    }
                    break;
            }

            if (!$src) {
                return null;
            }

            // Original dimensions
            $origW = imagesx($src);
            $origH = imagesy($src);

            // Maintain aspect ratio
            $ratio = $origW / $origH;
            if ($width / $height > $ratio) {
                $width = $height * $ratio;
            } else {
                $height = $width / $ratio;
            }

            $dst = imagecreatetruecolor($width, $height);

            // Preserve transparency for PNG/GIF/WEBP
            if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF || $type == 18) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $origW, $origH);

            // Capture output
            ob_start();
            // We use JPEG for the final output as it's typically smaller than PNG for PDFs
            imagejpeg($dst, null, $quality);
            $finalData = ob_get_clean();

            // Cleanup memory
            imagedestroy($src);
            imagedestroy($dst);

            if (!$finalData) {
                return null;
            }

            $base64 = 'data:image/jpeg;base64,' . base64_encode($finalData);
            self::$cache[$cacheKey] = $base64;

            return $base64;

        } catch (\Exception $e) {
            return null;
        }
    }
}
