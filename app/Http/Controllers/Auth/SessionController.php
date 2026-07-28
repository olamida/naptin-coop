<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            $token = Str::random(64);
            $user->update(['active_session_token' => $token]);
            session(['active_session_token' => $token]);

            ActivityLog::log('login', $user->name . ' logged in successfully');

            if ($user->member_id) {
                return redirect()->intended(route('portal.dashboard'));
            }

            return redirect()->intended(route('dashboard'));
        }

        ActivityLog::log('login_failed', 'Failed login attempt for: ' . $request->email);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::log('logout', $user->name . ' logged out');
            $user->update(['active_session_token' => null]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
