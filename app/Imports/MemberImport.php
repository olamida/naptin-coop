<?php

namespace App\Imports;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MemberImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): ?Member
    {
        $region = Region::where('name', 'like', '%' . $row['region'] . '%')->first();

        if (! $region) {
            $region = Region::firstOrCreate(
                ['name' => $row['region']],
                ['code' => Str::upper(substr($row['region'], 0, 3))]
            );
        }

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => $row['staff_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['phone'] ?? null,
            'gender' => $row['gender'] ?? null,
            'date_of_birth' => $row['date_of_birth'] ?? null,
            'employment_date' => $row['employment_date'] ?? null,
            'address' => $row['address'] ?? null,
            'state_of_origin' => $row['state_of_origin'] ?? null,
            'nin' => $row['nin'] ?? null,
            'grade_level' => $row['grade_level'] ?? null,
            'monthly_salary' => $row['monthly_salary'] ?? 0,
            'status' => $row['status'] ?? MemberStatus::ACTIVE->value,
        ]);

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV/' . Str::upper(Str::random(2)) . '/' . str_pad($member->id, 6, '0', STR_PAD_LEFT),
            'balance' => 0,
        ]);

        ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 0,
            'total_value' => 0,
        ]);

        return $member;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|string|unique:members,staff_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'region' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'employment_date' => 'nullable|date',
            'address' => 'nullable|string',
            'state_of_origin' => 'nullable|string|max:100',
            'nin' => 'nullable|string',
            'grade_level' => 'nullable|string|max:20',
            'monthly_salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,retired,suspended',
        ];
    }
}
