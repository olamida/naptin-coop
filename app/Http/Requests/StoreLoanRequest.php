<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'loan_product_id' => 'nullable|exists:loan_products,id',
            'type' => 'required|in:regular,emergency,educational,special',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'tenure_months' => 'required|integer|min:1|max:120',
            'purpose' => 'nullable|string|max:1000',
            'guarantor_ids' => 'nullable|array',
            'guarantor_ids.*' => 'exists:members,id',
        ];
    }
}
