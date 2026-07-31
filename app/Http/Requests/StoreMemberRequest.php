<?php

namespace App\Http\Requests;

use App\Enums\MemberStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_id' => 'required|exists:regions,id',
            'staff_id' => 'required|numeric|unique:members,staff_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'employment_date' => 'nullable|date',
            'retirement_date' => 'nullable|date|after_or_equal:employment_date',
            'address' => 'nullable|string',
            'state_of_origin' => 'nullable|string|max:100',
            'nin' => 'nullable|string|unique:members,nin',
            'grade_level' => 'nullable|string|max:20',
            'monthly_salary' => 'nullable|numeric|min:0',
            'monthly_savings' => 'nullable|numeric|min:0',
            'status' => 'required|in:' . implode(',', array_column(MemberStatus::cases(), 'value')),
            'photo' => 'nullable|image|max:2048',
        ];
    }
}
