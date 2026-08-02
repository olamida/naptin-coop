<?php

namespace App\Http\Controllers;

use App\Enums\GuarantorStatus;
use App\Models\LoanGuarantor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuaranteeController extends Controller
{
    public function show(string $token): View
    {
        $guarantor = LoanGuarantor::where('accept_token', $token)
            ->with(['loan.loanProduct', 'loan.member', 'member'])
            ->firstOrFail();

        if (! $guarantor->isValidToken()) {
            if ($guarantor->status === GuarantorStatus::Accepted) {
                return view('guarantee.accept', [
                    'guarantor' => $guarantor,
                    'alreadyResponded' => true,
                    'message' => 'You have already accepted this guarantor request.',
                ]);
            }
            if ($guarantor->status === GuarantorStatus::Declined) {
                return view('guarantee.accept', [
                    'guarantor' => $guarantor,
                    'alreadyResponded' => true,
                    'message' => 'You have already declined this guarantor request.',
                ]);
            }
            abort(419, 'This guarantor invitation has expired or is invalid.');
        }

        return view('guarantee.accept', [
            'guarantor' => $guarantor,
            'alreadyResponded' => false,
            'message' => null,
        ]);
    }

    public function respond(Request $request, string $token): RedirectResponse
    {
        $guarantor = LoanGuarantor::where('accept_token', $token)
            ->with('loan')
            ->firstOrFail();

        if (! $guarantor->isValidToken()) {
            return redirect()->route('guarantee.show', $token)
                ->with('error', 'This invitation has expired or already been responded to.');
        }

        $action = $request->input('action');

        if (! in_array($action, ['accept', 'decline'], true)) {
            return back()->with('error', 'Invalid action.');
        }

        $guarantor->update([
            'status' => $action === 'accept' ? GuarantorStatus::Accepted : GuarantorStatus::Declined,
            'responded_at' => now(),
            'accepted_ip' => $request->ip(),
            'accepted_user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('guarantee.show', $token)
            ->with('success', $action === 'accept'
                ? 'You have accepted the guarantor request. Thank you!'
                : 'You have declined the guarantor request.');
    }
}
