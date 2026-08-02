<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BrandingAsset;
use App\Services\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandingAssetController extends Controller
{
    public function __construct(private readonly BrandingService $branding) {}

    public function index(): View
    {
        $assets = BrandingAsset::query()->orderBy('key')->get()->keyBy('key');

        return view('admin.branding.index', [
            'assets' => $assets,
            'meta' => BrandingService::META,
        ]);
    }

    public function upload(Request $request, string $key): RedirectResponse
    {
        $this->assertKey($key);

        $request->validate([
            'asset' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $asset = $this->branding->upload($request->file('asset'), $key, auth()->id());

        ActivityLog::log('branding.upload', "Uploaded the {$asset->label} branding asset.", ['key' => $key]);

        return back()->with('success', "{$asset->label} updated successfully.");
    }

    public function regenerate(string $key): RedirectResponse
    {
        $this->assertKey($key);

        if (! $this->branding->regenerate($key)) {
            return back()->with('error', 'No asset exists for this key yet — upload one first.');
        }

        ActivityLog::log('branding.regenerate', "Regenerated variants for the {$key} branding asset.", ['key' => $key]);

        return back()->with('success', 'Branding variants regenerated successfully.');
    }

    public function destroy(string $key): RedirectResponse
    {
        $this->assertKey($key);

        $this->branding->delete($key);

        ActivityLog::log('branding.delete', "Removed the {$key} branding asset.", ['key' => $key]);

        return back()->with('success', 'Branding asset removed.');
    }

    public function preview(): View
    {
        return view('admin.branding.preview');
    }

    protected function assertKey(string $key): void
    {
        abort_unless(in_array($key, BrandingAsset::KEYS, true), 404);
    }
}
