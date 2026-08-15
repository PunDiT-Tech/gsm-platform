<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    public static function store(string $directory, UploadedFile $file, string $disk = 'local'): string
    {
        $webp = self::toWebP($file);

        if ($webp !== null) {
            $name = uniqid('img-', true) . '.webp';
            Storage::disk($disk)->put($directory . '/' . $name, $webp);

            return $directory . '/' . $name;
        }

        return $file->store($directory, $disk);
    }

    /**
     * Serve an image on the fly, optionally resized to fit within the given
     * dimensions and converted to WebP when the encoder is available. Results
     * are cached so each generated variant is only produced once.
     */
    public static function serve(string $disk, string $path, ?int $maxWidth = null, ?int $maxHeight = null): \Symfony\Component\HttpFoundation\Response
    {
        $storage = Storage::disk($disk);
        abort_unless($storage->exists($path), 404);

        $original = $storage->get($path);
        $cacheKey = self::cacheKey($path, $maxWidth, $maxHeight);
        $cached = self::variantPath($cacheKey);

        if ($storage->exists($cached)) {
            return self::response($storage->get($cached), $cacheKey, 'image/webp');
        }

        $out = self::transform($original, $maxWidth, $maxHeight);

        if ($out !== null && $out !== $original) {
            $storage->put($cached, $out);

            return self::response($out, $cacheKey, 'image/webp');
        }

        return self::response($original, $cacheKey, self::mimeFor($path, $original));
    }

    protected static function transform(string $binary, ?int $maxWidth, ?int $maxHeight): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $source = @imagecreatefromstring($binary);

        if (! $source) {
            return null;
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);

        if ($maxWidth || $maxHeight) {
            $ratio = $srcW / $srcH;
            if ($maxWidth && $maxHeight) {
                $w = min($srcW, $maxWidth);
                $h = min($srcH, $maxHeight);
            } elseif ($maxWidth) {
                $w = min($srcW, $maxWidth);
                $h = (int) round($w / $ratio);
            } else {
                $h = min($srcH, $maxHeight);
                $w = (int) round($h * $ratio);
            }

            $w = max(1, $w);
            $h = max(1, $h);

            if ($w !== $srcW || $h !== $srcH) {
                $resized = imagecreatetruecolor($w, $h);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $w, $h, $srcW, $srcH);
                $source = $resized;
            }
        }

        ob_start();
        $ok = imagewebp($source, null, 82);
        $data = ob_get_clean();

        if (! $ok || $data === false) {
            return null;
        }

        return $data;
    }

    protected static function response(string $binary, string $cacheKey, string $mime): \Symfony\Component\HttpFoundation\Response
    {
        return response($binary)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=31536000, immutable')
            ->header('X-Image-Variant', $cacheKey);
    }

    protected static function mimeFor(string $path, string $binary): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => (new \finfo())->buffer($binary) ?: 'application/octet-stream',
        };
    }

    protected static function cacheKey(string $path, ?int $maxWidth, ?int $maxHeight): string
    {
        return hash('sha256', $path . '|' . ($maxWidth ?? '') . '|' . ($maxHeight ?? ''));
    }

    protected static function variantPath(string $key): string
    {
        return 'image-cache/' . $key . '.webp';
    }

    protected static function toWebP(UploadedFile $file): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        $type = $file->getMimeType();

        $source = match ($type) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => null,
            default => null,
        };

        if (! $source) {
            return null;
        }

        ob_start();
        $ok = imagewebp($source, null, 82);
        $data = ob_get_clean();

        if (! $ok || $data === false) {
            return null;
        }

        return $data;
    }
}
