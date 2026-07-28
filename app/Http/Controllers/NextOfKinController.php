<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\NextOfKin;
use Illuminate\Http\Request;

class NextOfKinController extends Controller
{
    public function store(Request $request, Member $member): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'is_primary' => 'boolean',
        ]);

        if (!empty($validated['is_primary']) && $validated['is_primary']) {
            NextOfKin::where('member_id', $member->id)->update(['is_primary' => false]);
        }

        $member->nextOfKins()->create($validated);

        return redirect()->route('members.show', $member)
            ->with('success', 'Next of kin added successfully.');
    }

    public function destroy(Member $member, NextOfKin $nextOfKin): \Illuminate\Http\RedirectResponse
    {
        $nextOfKin->delete();

        return redirect()->route('members.show', $member)
            ->with('success', 'Next of kin removed successfully.');
    }
}
