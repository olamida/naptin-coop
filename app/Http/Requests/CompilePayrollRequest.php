<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompilePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'month_number' => 'required|integer|between:1,12',
        ];
    }
}
