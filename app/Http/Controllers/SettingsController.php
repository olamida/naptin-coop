<?php

namespace App\Http\Controllers;

use App\Models\BrandingAsset;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $company = Company::instance();

        $assets = BrandingAsset::query()->orderBy('key')->get()->keyBy('key');

        return view('admin.settings.edit', compact('company', 'assets'));
    }

    public function update(Request $request): RedirectResponse
    {
        $company = Company::instance();

        $validated = $request->validate([
            // General / Branding
            'name' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'short_history' => 'nullable|string|max:2000',
            'registration_number' => 'nullable|string|max:255',
            'footer_note' => 'nullable|string|max:500',

            // Contact
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'opening_hours' => 'nullable|string|max:255',

            // Social Media
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',

            // Theme
            'theme_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',

            // Media
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',

            // Financial
            'thrift_amount' => 'required|numeric|min:0',
            'membership_fee' => 'required|numeric|min:0',
            'savings_interest_rate' => 'required|numeric|min:0|max:100',
            'loan_interest_rate' => 'required|numeric|min:0|max:100',
            'max_loan_multiplier' => 'required|integer|min:1|max:20',
            'auto_approve_deposit_limit' => 'nullable|numeric|min:0',
        ]);

        $data = collect($validated)->except(['logo', 'banner'])->toArray();

        $data['theme_color'] = $request->filled('theme_color') ? $request->theme_color : '#2563eb';
        $data['secondary_color'] = $request->filled('secondary_color') ? $request->secondary_color : '#059669';

        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('company', 'public');
        }

        if ($request->boolean('remove_banner') && $company->banner_path) {
            Storage::disk('public')->delete($company->banner_path);
            $data['banner_path'] = null;
        }

        if ($request->hasFile('banner')) {
            if ($company->banner_path) {
                Storage::disk('public')->delete($company->banner_path);
            }
            $data['banner_path'] = $request->file('banner')->store('company/banners', 'public');
        }

        $company->update($data);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Company settings updated successfully.');
    }
}
