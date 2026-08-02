<?php

namespace App\Services;

use App\Models\BrandingAsset;
use App\Models\Company;
use App\Support\BrandingImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Central access point for the admin-managed branding assets.
 *
 * Assets are stored on the public disk under branding/{key}/ with generated
 * size variants in branding/{key}/variants. All lookups are cached and are
 * flushed whenever an asset is uploaded, deleted, or regenerated.
 */
class BrandingService
{
    public const VARIANTS = [
        'favicon' => ['method' => 'fit', 'prefix' => 'favicon', 'sizes' => [16, 32, 48, 180, 192, 512], 'ext' => 'png'],
        'hero_savings' => ['method' => 'resize', 'prefix' => 'hero', 'sizes' => [[1920, 720], [1280, 480], [640, 360]], 'ext' => 'webp'],
        'hero_unity' => ['method' => 'resize', 'prefix' => 'hero', 'sizes' => [[1920, 720], [1280, 480], [640, 360]], 'ext' => 'webp'],
        'hero_fintech' => ['method' => 'resize', 'prefix' => 'hero', 'sizes' => [[1920, 720], [1280, 480], [640, 360]], 'ext' => 'webp'],
        'logo_primary' => ['method' => 'fit', 'prefix' => 'logo', 'sizes' => [128, 256, 512], 'ext' => 'png'],
        'icon_round' => ['method' => 'fit', 'prefix' => 'icon', 'sizes' => [64, 128, 256, 512], 'ext' => 'png'],
    ];

    public const HERO_CONTEXT_MAP = [
        'login_admin' => 'hero_fintech',
        'login_member' => 'hero_savings',
        'homepage' => 'hero_unity',
        'dashboard' => 'hero_fintech',
        'savings' => 'hero_savings',
        'about' => 'hero_unity',
    ];

    public const META = [
        'favicon' => [
            'label' => 'Favicon',
            'description' => 'Browser tab icon and PWA app icon. A square image works best.',
            'recommended_size' => '512×512 (square, PNG)',
            'usage' => ['Browser tab', 'PWA home screen icon', 'Apple touch icon'],
        ],
        'hero_savings' => [
            'label' => 'Savings Hero',
            'description' => 'Wide banner used behind the member login and savings contexts.',
            'recommended_size' => '1920×720 (wide)',
            'usage' => ['Member login', 'Member dashboard'],
        ],
        'hero_unity' => [
            'label' => 'Unity Hero',
            'description' => 'Community and team imagery used on the public homepage and about page.',
            'recommended_size' => '1920×720 (wide)',
            'usage' => ['Homepage', 'About page'],
        ],
        'hero_fintech' => [
            'label' => 'Fintech Hero',
            'description' => 'Digital finance imagery used on the admin login and dashboard.',
            'recommended_size' => '1920×720 (wide)',
            'usage' => ['Admin login', 'Admin dashboard'],
        ],
        'logo_primary' => [
            'label' => 'Primary Logo',
            'description' => 'Full-colour logo for light backgrounds: header, receipts, emails, login card.',
            'recommended_size' => '512×512 or wider with transparency',
            'usage' => ['Public header', 'Receipts', 'Emails', 'Login card'],
        ],
        'icon_round' => [
            'label' => 'Round Icon',
            'description' => 'Compact square icon for dark sidebars and small avatar contexts.',
            'recommended_size' => '512×512 (square)',
            'usage' => ['Admin sidebar', 'Member sidebar'],
        ],
    ];

    public function get(string $key, string $variant = 'default'): ?string
    {
        if (! in_array($key, BrandingAsset::KEYS, true)) {
            return null;
        }

        return Cache::remember("branding.get.{$key}.{$variant}", 3600, function () use ($key, $variant) {
            $asset = BrandingAsset::query()
                ->where('key', $key)
                ->where('is_active', true)
                ->first();

            if (! $asset || ! $asset->file_path) {
                return null;
            }

            if ($variant === 'default') {
                return $asset->url;
            }

            return $asset->variantUrl($variant);
        });
    }

    /**
     * Best-fit logo for a rendering context.
     *
     * @param  string  $variant  header | sidebar | pdf | favicon
     */
    public function getLogo(string $variant = 'header'): ?string
    {
        return match ($variant) {
            'sidebar' => $this->get('icon_round', 'icon-128x128.png')
                ?? $this->get('logo_primary', 'logo-128x128.png'),
            'pdf' => $this->get('logo_primary', 'logo-256x256.png')
                ?? $this->get('icon_round', 'icon-256x256.png'),
            'favicon' => $this->get('favicon', 'favicon-32x32.png'),
            default => $this->get('logo_primary', 'logo-128x128.png')
                ?? $this->get('icon_round', 'icon-128x128.png')
                ?? $this->get('favicon', 'favicon-48x48.png'),
        };
    }

    /**
     * Hero image for a rendering context (login_admin, login_member, homepage, dashboard, savings, about).
     */
    public function getHero(string $context): ?string
    {
        $key = self::HERO_CONTEXT_MAP[$context] ?? 'hero_unity';

        return $this->get($key, 'hero-1920.webp') ?? $this->get($key);
    }

    /**
     * Preferred browser chrome / PWA theme colour.
     */
    public function getThemeColor(): string
    {
        return Company::instance()->theme_color ?: '#2563eb';
    }

    /**
     * Store a freshly uploaded file, generate all variants and warm the cache.
     */
    public function upload(UploadedFile $file, string $key, ?int $uploadedBy = null): BrandingAsset
    {
        $this->assertKey($key);

        $ext = strtolower($file->getClientOriginalExtension());
        $dir = 'branding/'.$key;
        Storage::disk('public')->deleteDirectory($dir);

        $stored = $file->storeAs($dir, 'original.'.$ext, 'public');
        if (! $stored) {
            throw new \RuntimeException('Could not store the branding asset.');
        }

        $asset = $this->saveRecord($key, $stored, $file->getMimeType() ?: 'image/'.$ext, $uploadedBy);
        $this->generateVariants($asset);
        $this->flushCache();

        return $asset->fresh();
    }

    /**
     * Import an asset from a local file path (used by the seeder and artisan commands).
     */
    public function seed(string $key, string $sourcePath, ?int $uploadedBy = null): BrandingAsset
    {
        $this->assertKey($key);

        if (! is_file($sourcePath)) {
            throw new \InvalidArgumentException("Source file does not exist: {$sourcePath}");
        }

        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $dir = 'branding/'.$key;
        Storage::disk('public')->deleteDirectory($dir);

        $stored = $dir.'/original.'.$ext;
        Storage::disk('public')->put($stored, file_get_contents($sourcePath));

        $asset = $this->saveRecord($key, $stored, mime_content_type($sourcePath) ?: 'image/'.$ext, $uploadedBy);
        $this->generateVariants($asset);
        $this->flushCache();

        return $asset->fresh();
    }

    /**
     * Rebuild the variant files from the stored original (keeps the record intact).
     */
    public function regenerate(string $key): bool
    {
        $this->assertKey($key);

        $asset = BrandingAsset::where('key', $key)->first();
        if (! $asset) {
            return false;
        }

        $this->generateVariants($asset);
        $this->flushCache();

        return true;
    }

    public function delete(string $key): void
    {
        $this->assertKey($key);

        Storage::disk('public')->deleteDirectory('branding/'.$key);
        BrandingAsset::where('key', $key)->delete();
        $this->flushCache();
    }

    public function flushCache(): void
    {
        foreach (BrandingAsset::KEYS as $key) {
            Cache::forget("branding.get.{$key}.default");
            foreach ($this->variantFilenames($key) as $filename) {
                Cache::forget("branding.get.{$key}.{$filename}");
            }
        }
    }

    /**
     * Build the variant files for an asset on the public disk. When the asset is
     * the favicon, the PWA icons and browser favicon are synced into public/ so
     * the existing manifest.json and service worker keep working untouched.
     */
    protected function generateVariants(BrandingAsset $asset): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $config = self::VARIANTS[$asset->key] ?? null;
        if (! $config) {
            return;
        }

        $original = Storage::disk('public')->path($asset->file_path);
        if (! is_file($original)) {
            return;
        }

        $variantDir = 'branding/'.$asset->key.'/variants';
        Storage::disk('public')->deleteDirectory($variantDir);

        foreach ($config['sizes'] as $size) {
            $filename = $this->filenameFor($config, $size);
            $dst = Storage::disk('public')->path($variantDir.'/'.$filename);

            if ($config['method'] === 'resize') {
                [$width, $height] = $size;
                BrandingImage::resize($original, $width, $height, $dst);
            } else {
                BrandingImage::fit($original, $size, $dst);
            }
        }

        if ($asset->key === 'favicon') {
            $this->syncFaviconToPublic();
        }
    }

    /**
     * Copy the generated favicon sizes into public/ so the static PWA assets
     * (favicon.ico, icon-192.png, icon-512.png, apple-touch-icon.png) always
     * reflect the currently uploaded favicon.
     */
    protected function syncFaviconToPublic(): void
    {
        $variantDir = Storage::disk('public')->path('branding/favicon/variants');

        if (extension_loaded('gd')) {
            BrandingImage::icoFromPngs([
                $variantDir.'/favicon-16x16.png',
                $variantDir.'/favicon-32x32.png',
                $variantDir.'/favicon-48x48.png',
            ], public_path('favicon.ico'));
        }

        $copies = [
            'favicon-192x192.png' => 'icon-192.png',
            'favicon-512x512.png' => 'icon-512.png',
            'favicon-180x180.png' => 'apple-touch-icon.png',
        ];

        foreach ($copies as $src => $dst) {
            $srcPath = $variantDir.'/'.$src;
            if (is_file($srcPath)) {
                copy($srcPath, public_path($dst));
            }
        }
    }

    protected function saveRecord(string $key, string $stored, ?string $mime, ?int $uploadedBy): BrandingAsset
    {
        $meta = self::META[$key] ?? [];

        return BrandingAsset::updateOrCreate(
            ['key' => $key],
            [
                'label' => $meta['label'] ?? ucwords(str_replace('_', ' ', $key)),
                'description' => $meta['description'] ?? null,
                'recommended_size' => $meta['recommended_size'] ?? null,
                'usage_locations' => $meta['usage'] ?? null,
                'file_path' => $stored,
                'file_type' => $mime,
                'is_active' => true,
                'uploaded_by' => $uploadedBy,
            ]
        );
    }

    /**
     * @return string[] filenames (without directory) that would be generated for a key
     */
    protected function variantFilenames(string $key): array
    {
        $config = self::VARIANTS[$key] ?? null;
        if (! $config) {
            return [];
        }

        return array_map(fn ($size) => $this->filenameFor($config, $size), $config['sizes']);
    }

    protected function filenameFor(array $config, int|array $size): string
    {
        if (is_array($size)) {
            return $config['prefix'].'-'.$size[0].'.'.$config['ext'];
        }

        return $config['prefix'].'-'.$size.'x'.$size.'.'.$config['ext'];
    }

    protected function assertKey(string $key): void
    {
        if (! in_array($key, BrandingAsset::KEYS, true)) {
            throw new \InvalidArgumentException("Unknown branding asset key: {$key}");
        }
    }
}
