<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        $regions = Region::where('enabled', true)->orderBy('name')->get();

        return view('auth.register', compact('regions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:members,email',
            'phone' => 'required|string|max:20',
            'region_id' => 'required|exists:regions,id',
            'staff_id' => 'required|numeric|unique:members,staff_id',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'nin' => 'nullable|string|unique:members,nin',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('member-photos', 'public');
        }

        $member = Member::create(array_merge($validated, [
            'photo_path' => $photoPath,
            'status' => 'pending',
        ]));

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV/'.Str::upper(Str::random(2)).'/'.str_pad($member->id, 6, '0', STR_PAD_LEFT),
            'balance' => 0,
        ]);

        ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 0,
            'total_value' => 0,
        ]);

        return redirect()->route('login')
            ->with('success', 'Registration submitted successfully! Your account is pending admin approval. You will receive an email once approved.');
    }
}
