<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $regions = Region::withCount('members')->orderBy('name')->paginate(15);

        return view('admin.regions.index', ['regions' => $regions]);
    }

    public function create(): \Illuminate\View\View
    {
        return view('admin.regions.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:regions,name',
            'code' => 'required|string|max:20|unique:regions,code',
            'zone' => 'nullable|string|max:255',
            'headquarters' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Region::create($validated);

        return redirect()->route('admin.regions.index')
            ->with('success', 'Region created successfully.');
    }

    public function edit(Region $region): \Illuminate\View\View
    {
        return view('admin.regions.edit', ['region' => $region]);
    }

    public function update(Request $request, Region $region): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:regions,name,' . $region->id,
            'code' => 'required|string|max:20|unique:regions,code,' . $region->id,
            'zone' => 'nullable|string|max:255',
            'headquarters' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $region->update($validated);

        return redirect()->route('admin.regions.index')
            ->with('success', 'Region updated successfully.');
    }

    public function destroy(Region $region): \Illuminate\Http\RedirectResponse
    {
        $region->delete();

        return redirect()->route('admin.regions.index')
            ->with('success', 'Region deleted successfully.');
    }
}
