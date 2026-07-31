<?php

namespace App\Imports;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use App\Imports\Concerns\TracksImportStats;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MemberImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use TracksImportStats;

    public function __construct(
        public ?string $batchId = null,
    ) {}

    public function model(array $row): ?Member
    {
        $this->trackRow();

        foreach (['staff_id', 'first_name', 'last_name', 'middle_name', 'region', 'email', 'phone', 'gender', 'address', 'state_of_origin', 'nin', 'grade_level', 'status', 'external_reference'] as $field) {
            if (isset($row[$field]) && $row[$field] !== null) {
                $row[$field] = (string) $row[$field];
            }
        }

        if (!empty($row['external_reference']) && Member::where('external_reference', $row['external_reference'])->exists()) {
            $this->markFailure('Duplicate external reference "' . $row['external_reference'] . '" — skipped');

            return null;
        }

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
            'status' => $row['status'] ?? MemberStatus::Active->value,
            'import_batch_id' => $this->batchId,
            'external_reference' => $row['external_reference'] ?? null,
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

        $this->markSuccess();

        return $member;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|unique:members,staff_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'region' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'employment_date' => 'nullable|date',
            'address' => 'nullable|string',
            'state_of_origin' => 'nullable|string|max:100',
            'nin' => 'nullable|max:100',
            'grade_level' => 'nullable|max:20',
            'monthly_salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,retired,suspended',
            'external_reference' => 'nullable|string|max:100',
        ];
    }
}
