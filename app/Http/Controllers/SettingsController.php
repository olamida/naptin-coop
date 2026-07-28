<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit(): \Illuminate\View\View
    {
        $company = Company::instance();

        return view('admin.settings.edit', compact('company'));
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $company = Company::instance();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'thrift_amount' => 'required|numeric|min:0',
            'membership_fee' => 'required|numeric|min:0',
            'savings_interest_rate' => 'required|numeric|min:0|max:100',
            'loan_interest_rate' => 'required|numeric|min:0|max:100',
            'max_loan_multiplier' => 'required|integer|min:1|max:20',
            'footer_note' => 'nullable|string|max:500',
        ]);

        $data = collect($validated)->except(['logo'])->toArray();

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

        $company->update($data);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Company settings updated successfully.');
    }
}
