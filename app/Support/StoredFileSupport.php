<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StoredFileSupport
{
    public const PRIVATE_DISK = 'local';
    public const LEGACY_PUBLIC_DISK = 'public';

    public static function storePrivateFile(UploadedFile $file, string $directory, string $filename): string
    {
        return $file->storeAs(trim($directory, '/'), $filename, self::PRIVATE_DISK);
    }

    public static function exists(?string $path): bool
    {
        return self::absolutePath($path) !== null;
    }

    public static function delete(?string $path): bool
    {
        $disk = self::detectDisk($path);

        if (!$disk || !$path) {
            $absolutePath = self::legacyPublicPath($path);

            if (!$absolutePath || !is_file($absolutePath)) {
                return false;
            }

            return unlink($absolutePath);
        }

        return Storage::disk($disk)->delete($path);
    }

    public static function download(?string $path, ?string $downloadName = null): ?BinaryFileResponse
    {
        $absolutePath = self::absolutePath($path);

        if (!$absolutePath || !$path) {
            return null;
        }

        return response()->download($absolutePath, $downloadName ?: basename($path));
    }

    public static function inline(?string $path, array $headers = []): ?BinaryFileResponse
    {
        $absolutePath = self::absolutePath($path);

        if (!$absolutePath) {
            return null;
        }

        return response()->file($absolutePath, $headers);
    }

    public static function absolutePath(?string $path): ?string
    {
        $disk = self::detectDisk($path);

        if (!$disk || !$path) {
            return self::legacyPublicPath($path);
        }

        return Storage::disk($disk)->path($path);
    }

    public static function detectDisk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        foreach ([self::PRIVATE_DISK, self::LEGACY_PUBLIC_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    private static function legacyPublicPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $absolutePath = public_path(ltrim($path, '/'));

        return is_file($absolutePath) ? $absolutePath : null;
    }
}
