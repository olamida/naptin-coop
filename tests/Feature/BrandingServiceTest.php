<?php

namespace Tests\Feature;

use App\Models\BrandingAsset;
use App\Models\User;
use App\Services\BrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BrandingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super-admin']));
        $token = 'test-session-'.uniqid();
        $admin->forceFill(['active_session_token' => $token])->save();

        return [$admin, $token];
    }

    private function pngSource(string $suffix = ''): string
    {
        $path = tempnam(sys_get_temp_dir(), 'branding-'.$suffix).'.png';
        $img = imagecreatetruecolor(96, 96);
        imagefill($img, 0, 0, imagecolorallocate($img, 30, 64, 175));
        imagepng($img, $path);
        imagedestroy($img);

        return $path;
    }

    public function test_get_returns_null_when_no_asset_exists(): void
    {
        Storage::fake('public');

        $service = app(BrandingService::class);

        $this->assertNull($service->get('logo_primary'));
        $this->assertNull($service->get('logo_primary', 'logo-128x128.png'));
        $this->assertNull($service->getHero('login_admin'));
        $this->assertNull($service->getLogo('header'));
    }

    public function test_get_returns_null_for_unknown_key(): void
    {
        Storage::fake('public');

        $this->assertNull(app(BrandingService::class)->get('not_a_key'));
    }

    public function test_seed_creates_asset_and_generates_variants(): void
    {
        Storage::fake('public');

        $service = app(BrandingService::class);
        $asset = $service->seed('logo_primary', $this->pngSource());

        $this->assertDatabaseHas('branding_assets', ['key' => 'logo_primary']);
        $this->assertTrue(Storage::disk('public')->exists($asset->file_path));

        foreach (['logo-128x128.png', 'logo-256x256.png', 'logo-512x512.png'] as $variant) {
            $this->assertTrue(
                Storage::disk('public')->exists('branding/logo_primary/variants/'.$variant),
                "Missing variant {$variant}"
            );
        }

        $this->assertStringContainsString('logo-128x128.png', (string) $service->get('logo_primary', 'logo-128x128.png'));
        $this->assertStringContainsString('logo-128x128.png', (string) $service->getLogo('header'));
    }

    public function test_upload_stores_file_and_warm_cache(): void
    {
        Storage::fake('public');

        $service = app(BrandingService::class);
        $asset = $service->upload(UploadedFile::fake()->image('hero.jpg', 1200, 400), 'hero_fintech');

        $this->assertDatabaseHas('branding_assets', ['key' => 'hero_fintech']);
        $this->assertTrue(Storage::disk('public')->exists($asset->file_path));
        $this->assertTrue(Storage::disk('public')->exists('branding/hero_fintech/variants/hero-1920.webp'));

        $this->assertStringContainsString('branding/hero_fintech/variants/hero-1920.webp', (string) $service->getHero('login_admin'));
        $this->assertStringContainsString('branding/hero_fintech/variants/hero-1920.webp', (string) $service->getHero('dashboard'));
    }

    public function test_get_hero_maps_contexts_to_correct_asset(): void
    {
        Storage::fake('public');

        $service = app(BrandingService::class);
        $service->seed('hero_savings', $this->pngSource());
        $service->seed('hero_unity', $this->pngSource());

        $this->assertStringContainsString('branding/hero_savings/variants/hero-1920.webp', (string) $service->getHero('login_member'));
        $this->assertStringContainsString('branding/hero_savings/variants/hero-1920.webp', (string) $service->getHero('savings'));
        $this->assertStringContainsString('branding/hero_unity/variants/hero-1920.webp', (string) $service->getHero('homepage'));
        $this->assertStringContainsString('branding/hero_unity/variants/hero-1920.webp', (string) $service->getHero('about'));
    }

    public function test_get_logo_falls_back_across_variants(): void
    {
        Storage::fake('public');

        $service = app(BrandingService::class);
        $service->seed('logo_primary', $this->pngSource());

        $this->assertStringContainsString('logo-128x128.png', (string) $service->getLogo('header'));
        $this->assertStringContainsString('logo-256x256.png', (string) $service->getLogo('pdf'));
    }

    public function test_regenerate_rebuilds_missing_variants(): void
    {
        Storage::fake('public');

        $service = app(BrandingService::class);
        $service->seed('icon_round', $this->pngSource());
        $variant = Storage::disk('public')->path('branding/icon_round/variants/icon-128x128.png');
        $this->assertFileExists($variant);

        unlink($variant);
        $this->assertFalse(Storage::disk('public')->exists('branding/icon_round/variants/icon-128x128.png'));

        $this->assertTrue($service->regenerate('icon_round'));
        $this->assertTrue(Storage::disk('public')->exists('branding/icon_round/variants/icon-128x128.png'));

        $this->assertFalse($service->regenerate('hero_unity'));
    }

    public function test_delete_removes_asset_and_flushes_cache(): void
    {
        Storage::fake('public');

        $service = app(BrandingService::class);
        $service->seed('logo_primary', $this->pngSource());
        $this->assertNotNull($service->get('logo_primary'));

        $service->delete('logo_primary');

        $this->assertDatabaseMissing('branding_assets', ['key' => 'logo_primary']);
        $this->assertFalse(Storage::disk('public')->exists('branding/logo_primary'));
        $this->assertNull($service->get('logo_primary'));
    }

    public function test_favicon_upload_syncs_public_pwa_icons(): void
    {
        Storage::fake('public');

        $tracked = ['favicon.ico', 'icon-192.png', 'icon-512.png', 'apple-touch-icon.png'];
        $backups = [];

        foreach ($tracked as $name) {
            $path = public_path($name);
            if (is_file($path)) {
                $backups[$name] = file_get_contents($path);
            }
        }

        try {
            app(BrandingService::class)->upload(UploadedFile::fake()->image('fav.png', 64, 64), 'favicon');

            foreach (BrandingAsset::FAVICON_SIZES as $size) {
                $this->assertTrue(
                    Storage::disk('public')->exists("branding/favicon/variants/favicon-{$size}x{$size}.png"),
                    "Missing favicon size {$size}"
                );
            }

            foreach ($tracked as $name) {
                $this->assertFileExists(public_path($name));
            }

            $this->assertStringContainsString('favicon-32x32.png', (string) app(BrandingService::class)->getLogo('favicon'));
        } finally {
            foreach ($tracked as $name) {
                $path = public_path($name);
                if (array_key_exists($name, $backups)) {
                    file_put_contents($path, $backups[$name]);
                } elseif (is_file($path)) {
                    unlink($path);
                }
            }
        }
    }

    public function test_login_page_renders_with_branding(): void
    {
        Storage::fake('public');

        $this->get(route('login'))->assertOk();
    }

    public function test_admin_can_upload_asset_via_endpoint(): void
    {
        Storage::fake('public');

        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('admin.branding.upload', 'icon_round'), [
                'asset' => UploadedFile::fake()->image('icon.png', 64, 64),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('branding_assets', ['key' => 'icon_round']);
        $this->assertNotNull(app(BrandingService::class)->get('icon_round'));
    }

    public function test_endpoint_rejects_unknown_key(): void
    {
        Storage::fake('public');

        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('admin.branding.upload', 'bogus'), [
                'asset' => UploadedFile::fake()->image('x.png'),
            ])
            ->assertNotFound();
    }
}
