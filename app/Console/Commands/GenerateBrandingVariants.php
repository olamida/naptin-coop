<?php

namespace App\Console\Commands;

use App\Models\BrandingAsset;
use App\Services\BrandingService;
use Illuminate\Console\Command;

class GenerateBrandingVariants extends Command
{
    protected $signature = 'branding:generate';

    protected $description = 'Regenerate all branding size variants from the stored originals';

    public function handle(): int
    {
        $service = app(BrandingService::class);
        $assets = BrandingAsset::query()->orderBy('key')->get();

        if ($assets->isEmpty()) {
            $this->warn('No branding assets found. Run "php artisan branding:seed" first.');

            return self::SUCCESS;
        }

        foreach ($assets as $asset) {
            if ($service->regenerate($asset->key)) {
                $this->info("Regenerated variants for: {$asset->key}");
            } else {
                $this->error("Could not regenerate: {$asset->key}");
            }
        }

        return self::SUCCESS;
    }
}
