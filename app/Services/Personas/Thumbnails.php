<?php

namespace App\Services\Personas;

class Thumbnails
{
    /**
     * Return absolute path to a 128x128 thumbnail for a persona image.
     * Generates + caches to disk if missing.
     */
    public static function path(string $personaId, int $size = 128, ?string $imageDir = null): ?string
    {
        $imageDir ??= config('personas.image_path');
        $source = "{$imageDir}/{$personaId}.png";
        if (!is_file($source)) return null;

        $thumbDir = "{$imageDir}/thumbs";
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0775, true);
        $thumbPath = "{$thumbDir}/{$personaId}-{$size}.jpg";

        if (is_file($thumbPath) && filemtime($thumbPath) >= filemtime($source)) {
            return $thumbPath;
        }

        $src = @imagecreatefrompng($source);
        if (!$src) return null;

        $w = imagesx($src); $h = imagesy($src);
        $dst = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        // Fit-cover (center crop to square)
        $srcMin = min($w, $h);
        $srcX = (int) (($w - $srcMin) / 2);
        $srcY = (int) (($h - $srcMin) / 2);
        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $srcMin, $srcMin);

        imagejpeg($dst, $thumbPath, 82);
        imagedestroy($src);
        imagedestroy($dst);
        return $thumbPath;
    }
}
