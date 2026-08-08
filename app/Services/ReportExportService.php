<?php

namespace App\Services;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Report export helpers: canonical content hashing + QR rendering.
 *
 * Every exported report (Excel or PDF) is stamped with a SHA-256 hash of its
 * canonical dataset so its contents can be re-verified later. The PDF exports
 * embed the hash inside a QR code (GD-backed PNG, no Imagick required) plus the
 * plain hash string for manual comparison.
 */
class ReportExportService
{
    public function hash(string $report, array $data): string
    {
        return hash('sha256', json_encode([
            'report' => $report,
            'data' => $this->normalize($data),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Recursively cast booleans/null to native JSON values and sort associative
     * keys so the same dataset always produces the same hash.
     */
    private function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalize($item);
            }

            if (array_keys($value) !== range(0, count($value) - 1)) {
                ksort($value);
            }
        }

        return $value;
    }

    public function qrPngDataUri(string $content, int $size = 200): string
    {
        $writer = new Writer(new GDLibRenderer($size, 4, 'png'));

        return 'data:image/png;base64,'.base64_encode($writer->writeString($content));
    }

    public function qrSvg(string $content, int $size = 120): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size), new SvgImageBackEnd));

        return $writer->writeString($content);
    }
}
