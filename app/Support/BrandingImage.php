<?php

namespace App\Support;

class BrandingImage
{
    public static function load(string $path): ?\GdImage
    {
        $info = @getimagesize($path);

        if ($info === false) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            IMAGETYPE_GIF => imagecreatefromgif($path),
            default => null,
        };
    }

    public static function save(\GdImage $img, string $path, int $quality = 85): bool
    {
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => imagejpeg($img, $path, $quality),
            'webp' => function_exists('imagewebp') ? imagewebp($img, $path, $quality) : false,
            default => self::savePng($img, $path),
        };
    }

    /**
     * Cover-crop the source image to an exact width x height.
     */
    public static function resize(string $src, int $width, int $height, string $dst): bool
    {
        $img = self::load($src);
        if ($img === null) {
            return false;
        }

        $srcWidth = imagesx($img);
        $srcHeight = imagesy($img);
        $scale = max($width / $srcWidth, $height / $srcHeight);
        $resizedWidth = max(1, (int) round($srcWidth * $scale));
        $resizedHeight = max(1, (int) round($srcHeight * $scale));

        $tmp = self::blank($resizedWidth, $resizedHeight);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $srcWidth, $srcHeight);

        $out = self::blank($width, $height);
        imagecopy($out, $tmp, 0, 0, (int) (($resizedWidth - $width) / 2), (int) (($resizedHeight - $height) / 2), $width, $height);

        $ok = self::save($out, $dst);

        imagedestroy($tmp);
        imagedestroy($out);
        imagedestroy($img);

        return $ok;
    }

    /**
     * Fit the source image inside a square canvas preserving aspect ratio.
     */
    public static function fit(string $src, int $size, string $dst): bool
    {
        $img = self::load($src);
        if ($img === null) {
            return false;
        }

        $srcWidth = imagesx($img);
        $srcHeight = imagesy($img);
        $scale = min($size / $srcWidth, $size / $srcHeight);
        $fitWidth = max(1, (int) round($srcWidth * $scale));
        $fitHeight = max(1, (int) round($srcHeight * $scale));

        $out = self::blank($size, $size);
        imagecopyresampled($out, $img, (int) (($size - $fitWidth) / 2), (int) (($size - $fitHeight) / 2), 0, 0, $fitWidth, $fitHeight, $srcWidth, $srcHeight);

        $ok = self::save($out, $dst);

        imagedestroy($out);
        imagedestroy($img);

        return $ok;
    }

    /**
     * Build a multi-size .ico file from existing PNG sources (16/32/48 best practice).
     */
    public static function icoFromPngs(array $pngPaths, string $icoPath): bool
    {
        $images = [];

        foreach ($pngPaths as $pngPath) {
            if (! is_file($pngPath)) {
                continue;
            }

            $img = self::load($pngPath);
            if ($img === null) {
                continue;
            }

            $images[] = [
                'width' => imagesx($img),
                'height' => imagesy($img),
                'bmp' => self::pngToBmpData($img),
            ];
            imagedestroy($img);
        }

        if ($images === []) {
            return false;
        }

        $dir = dirname($icoPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $count = count($images);
        $offset = 6 + (16 * $count);
        $entries = '';
        $data = '';

        foreach ($images as $image) {
            $bytes = strlen($image['bmp']);
            $entries .= pack(
                'CCCCvvVV',
                $image['width'] === 256 ? 0 : $image['width'],
                $image['height'] === 256 ? 0 : $image['height'],
                0,
                0,
                1,
                32,
                $bytes,
                $offset
            );
            $data .= $image['bmp'];
            $offset += $bytes;
        }

        return file_put_contents($icoPath, pack('vvv', 0, 1, $count).$entries.$data) !== false;
    }

    protected static function blank(int $width, int $height): \GdImage
    {
        $img = imagecreatetruecolor($width, $height);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);
        imagesavealpha($img, true);

        return $img;
    }

    protected static function savePng(\GdImage $img, string $path): bool
    {
        imagealphablending($img, false);
        imagesavealpha($img, true);

        return imagepng($img, $path, 6);
    }

    protected static function pngToBmpData(\GdImage $img): string
    {
        $width = imagesx($img);
        $height = imagesy($img);
        $header = pack('VVVvvVVVVVV', 40, $width, $height * 2, 1, 32, 0, $width * $height * 4, 0, 0, 0, 0);

        $pixels = '';
        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($img, $x, $y);
                $alpha = 255 - ((($color >> 24) & 0x7F) * 2);
                $red = ($color >> 16) & 0xFF;
                $green = ($color >> 8) & 0xFF;
                $blue = $color & 0xFF;
                $pixels .= pack('CCCC', $blue, $green, $red, $alpha);
            }
        }

        $andMask = str_repeat("\x00", (int) ceil($width / 32) * 4 * $height);

        return $header.$pixels.$andMask;
    }
}
