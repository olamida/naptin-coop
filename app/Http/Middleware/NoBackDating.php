<?php

namespace App\Http\Middleware;

use App\Models\PeriodClose;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards money routes against back-dated / future-dated transactions.
 *
 * Applies to request fields that carry a transaction date (entry_date,
 * transaction_date, payment_date, count_date, txn_date). A date that falls
 * after today (future) or on/before the last closed financial period is
 * rejected so ledgers cannot be rewritten after the books are locked.
 */
class NoBackDating
{
    protected const DATE_FIELDS = [
        'entry_date',
        'transaction_date',
        'payment_date',
        'count_date',
        'txn_date',
    ];

    public function handle(Request $request, Closure $next): RedirectResponse|JsonResponse|Response
    {
        if (! $request->isMethod('post') && ! $request->isMethod('put') && ! $request->isMethod('patch')) {
            return $next($request);
        }

        $lastClosedPeriod = PeriodClose::query()
            ->where('is_closed', true)
            ->max('period');

        foreach (self::DATE_FIELDS as $field) {
            $value = $request->input($field);

            if ($value === null || $value === '') {
                continue;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                continue;
            }

            try {
                $date = Carbon::parse($value);
            } catch (\Throwable $e) {
                // Let the form's validation rule report malformed dates.
                continue;
            }

            if ($date->greaterThan(now())) {
                return $this->reject(
                    $request,
                    $field,
                    "The {$field} cannot be in the future."
                );
            }

            $period = $date->format('Y-m');

            if ($lastClosedPeriod && $period <= $lastClosedPeriod) {
                return $this->reject(
                    $request,
                    $field,
                    "Transactions cannot be dated on or before the last closed period ({$lastClosedPeriod})."
                );
            }
        }

        return $next($request);
    }

    private function reject(Request $request, string $field, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => [$field => [$message]]], 422);
        }

        return back()
            ->withInput()
            ->withErrors([$field => $message]);
    }
}
