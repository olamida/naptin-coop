<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function challenge()
    {
        $user = auth()->user();

        if (!$user->totp_enabled) {
            return redirect()->route('two-factor.setup');
        }

        if (session('two_factor_verified')) {
            return redirect()->intended($user->member_id ? route('portal.dashboard') : route('dashboard'));
        }

        return view('two-factor.challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = auth()->user();

        if (!$user->totp_secret) {
            return back()->withErrors(['code' => 'Two-factor authentication is not configured.']);
        }

        $valid = $this->google2fa->verifyKey($user->totp_secret, $request->code);

        if (!$valid) {
            ActivityLog::log('two_factor_failed', "Failed 2FA attempt for {$user->email}");

            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        session(['two_factor_verified' => true]);

        ActivityLog::log('two_factor_verified', "2FA verified for {$user->email}");

        return redirect()->intended($user->member_id ? route('portal.dashboard') : route('dashboard'))
            ->with('success', 'Two-factor authentication verified.');
    }

    public function useRecoveryCode(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = auth()->user();

        if (!$user->totp_recovery_codes || !is_array($user->totp_recovery_codes)) {
            return back()->withErrors(['code' => 'No recovery codes available.']);
        }

        $codes = $user->totp_recovery_codes;
        $found = false;

        foreach ($codes as $i => $recoveryCode) {
            if (hash_equals($recoveryCode, $request->code)) {
                unset($codes[$i]);
                $found = true;
                break;
            }
        }

        if (!$found) {
            return back()->withErrors(['code' => 'Invalid recovery code.']);
        }

        $user->update(['totp_recovery_codes' => array_values($codes)]);

        session(['two_factor_verified' => true]);

        ActivityLog::log('two_factor_recovery', "2FA bypassed via recovery code for {$user->email}");

        return redirect()->intended($user->member_id ? route('portal.dashboard') : route('dashboard'))
            ->with('success', 'Two-factor authentication verified via recovery code.');
    }

    public function setup()
    {
        $user = auth()->user();

        if ($user->totp_enabled) {
            return redirect()->route('two-factor.challenge')
                ->with('info', 'Two-factor authentication is already enabled.');
        }

        $secret = session('pending_totp_secret') ?? $this->google2fa->generateSecretKey();
        session(['pending_totp_secret' => $secret]);

        $company = \App\Models\Company::instance();
        $appName = str_replace(' ', '', $company->name ?? 'NAPTIN Cooperative');
        $qrUrl = $this->google2fa->getQRCodeUrl($appName, $user->email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($qrUrl);

        return view('two-factor.setup', compact('secret', 'qrSvg'));
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = auth()->user();
        $secret = session('pending_totp_secret');

        if (!$secret) {
            return redirect()->route('two-factor.setup')
                ->withErrors(['code' => 'Session expired. Please start again.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(
                substr(hash('sha256', $secret . $i . $user->id), 0, 10)
            );
        }

        $user->update([
            'totp_secret' => $secret,
            'totp_enabled' => true,
            'totp_recovery_codes' => $recoveryCodes,
            'totp_confirmed_at' => now(),
        ]);

        session()->forget('pending_totp_secret');
        session(['two_factor_verified' => true, 'show_recovery_codes' => true]);

        ActivityLog::log('two_factor_enabled', "2FA enabled for {$user->email}");

        return redirect()->route('two-factor.recovery-codes')
            ->with('success', 'Two-factor authentication enabled successfully.');
    }

    public function showRecoveryCodes()
    {
        $user = auth()->user();
        $codes = $user->totp_recovery_codes ?? [];
        $show = session('show_recovery_codes', false);

        return view('two-factor.recovery-codes', compact('codes', 'show'));
    }

    public function generateRecoveryCodes(Request $request)
    {
        $request->validate([
            'confirm_password' => ['required', function ($attribute, $value, $fail) {
                if (!\Illuminate\Support\Facades\Hash::check($value, auth()->user()->password)) {
                    $fail('Password is incorrect.');
                }
            }],
        ]);

        $user = auth()->user();
        $newCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $newCodes[] = strtoupper(
                substr(hash('sha256', $user->totp_secret . $i . now()->timestamp), 0, 10)
            );
        }

        $user->update(['totp_recovery_codes' => $newCodes]);

        return back()->with('success', 'New recovery codes generated. Save them securely.');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'confirm_password' => ['required', function ($attribute, $value, $fail) {
                if (!\Illuminate\Support\Facades\Hash::check($value, auth()->user()->password)) {
                    $fail('Password is incorrect.');
                }
            }],
        ]);

        $user = auth()->user();

        $user->update([
            'totp_secret' => null,
            'totp_enabled' => false,
            'totp_recovery_codes' => null,
            'totp_confirmed_at' => null,
        ]);

        session()->forget('two_factor_verified');

        ActivityLog::log('two_factor_disabled', "2FA disabled for {$user->email}");

        return back()->with('success', 'Two-factor authentication disabled.');
    }
}
