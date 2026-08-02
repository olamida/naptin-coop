<?php

namespace App\Http\Controllers;

use App\Models\LoanProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanProductController extends Controller
{
    public function index(): View
    {
        $products = LoanProduct::latest()->get();

        return view('admin.loan-products.index', ['products' => $products]);
    }

    public function create(): View
    {
        return view('admin.loan-products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:loan_products,slug',
            'description' => 'nullable|string|max:1000',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0|gte:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'repayment_method' => 'required|in:flat,reducing_balance',
            'max_term_months' => 'required|integer|min:1|max:120',
            'max_loans_per_member' => 'nullable|integer|min:1',
            'max_total_amount_per_member' => 'nullable|numeric|min:0',
            'processing_fee_pct' => 'nullable|numeric|min:0|max:100',
            'requires_guarantors' => 'boolean',
            'requires_collateral' => 'boolean',
            'enabled' => 'boolean',
        ]);

        LoanProduct::create($validated);

        return redirect()->route('admin.loan-products.index')
            ->with('success', 'Loan product created successfully.');
    }

    public function edit(LoanProduct $loanProduct): View
    {
        return view('admin.loan-products.edit', ['product' => $loanProduct]);
    }

    public function update(Request $request, LoanProduct $loanProduct): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'required|numeric|min:0|gte:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'repayment_method' => 'required|in:flat,reducing_balance',
            'max_term_months' => 'required|integer|min:1|max:120',
            'max_loans_per_member' => 'nullable|integer|min:1',
            'max_total_amount_per_member' => 'nullable|numeric|min:0',
            'processing_fee_pct' => 'nullable|numeric|min:0|max:100',
            'requires_guarantors' => 'boolean',
            'requires_collateral' => 'boolean',
            'enabled' => 'boolean',
        ]);

        $loanProduct->update($validated);

        return redirect()->route('admin.loan-products.index')
            ->with('success', 'Loan product updated successfully.');
    }

    public function destroy(LoanProduct $loanProduct): RedirectResponse
    {
        $loanProduct->delete();

        return redirect()->route('admin.loan-products.index')
            ->with('success', 'Loan product deleted successfully.');
    }
}
