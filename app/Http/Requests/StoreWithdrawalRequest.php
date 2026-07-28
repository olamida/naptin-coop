<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
