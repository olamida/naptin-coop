<?php

namespace App\Console\Commands;

use Database\Seeders\BrandingAssetSeeder;
use Illuminate\Console\Command;

class SeedBrandingAssets extends Command
{
    protected $signature = 'branding:seed';

    protected $description = 'Seed the six branding assets from resources/branding/seed';

    public function handle(): int
    {
        $this->callSilently(BrandingAssetSeeder::class);

        $this->info('Branding assets seeded and variants generated.');

        return self::SUCCESS;
    }
}
