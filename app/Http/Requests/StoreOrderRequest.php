<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'payment_type' => 'required|in:cash,hire_purchase',
            'monthly_repayment' => 'required_if:payment_type,hire_purchase|nullable|numeric|min:0',
        ];
    }
}
