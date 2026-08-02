<?php

namespace Database\Seeders;

use App\Services\BrandingService;
use Illuminate\Database\Seeder;

class BrandingAssetSeeder extends Seeder
{
    /**
     * Source images were copied from the client's Gallery/ folder into
     * resources/branding/seed/. hero_savings temporarily reuses the unity
     * banner until the client supplies a dedicated savings image.
     */
    public function run(): void
    {
        $service = app(BrandingService::class);
        $base = resource_path('branding/seed');

        $sources = [
            'favicon' => 'handshake_icon-small.jpg',
            'hero_savings' => 'nigerian_team_unity_banner.jpg',
            'hero_unity' => 'nigerian_team_unity_banner.jpg',
            'hero_fintech' => 'fintech_hero_banner.jpg',
            'logo_primary' => 'naptin_staff_thrift_logo.jpg',
            'icon_round' => 'handshake_icon-small.jpg',
        ];

        foreach ($sources as $key => $file) {
            $path = $base.'/'.$file;

            if (! is_file($path)) {
                $this->command?->warn("Skipping {$key}: missing {$path}");

                continue;
            }

            $service->seed($key, $path);
            $this->command?->info("Seeded branding asset: {$key}");
        }
    }
}
