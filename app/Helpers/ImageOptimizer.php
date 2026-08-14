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