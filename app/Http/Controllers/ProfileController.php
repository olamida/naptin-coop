<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $isMember = $user->hasRole('member');

        return view($isMember ? 'profile.member-edit' : 'profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = $validated['password'];
        }

        if ($request->boolean('remove_photo') && $user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $data['profile_photo_path'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update($data);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }
}
