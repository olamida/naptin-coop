<?php

namespace Tests\Feature;

use App\Enums\GuarantorStatus;
use App\Models\Loan;
use App\Models\LoanGuarantor;
use App\Models\Member;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoanShowPageGuarantorStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_show_page_renders_guarantor_enum_statuses(): void
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF-'.substr(uniqid(), -6),
            'first_name' => 'Borrower',
            'last_name' => 'One',
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        $guarantorMember = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STAFF-'.substr(uniqid(), -6),
            'first_name' => 'Guarantor',
            'last_name' => 'Two',
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        $loan = Loan::create([
            'member_id' => $member->id,
            'loan_number' => 'LN-'.substr(uniqid(), -6),
            'type' => 'regular',
            'amount' => 50000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 4375,
            'outstanding' => 50000,
            'processing_fee' => 0,
            'application_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        LoanGuarantor::create([
            'loan_id' => $loan->id,
            'member_id' => $guarantorMember->id,
            'status' => GuarantorStatus::Declined,
            'responded_at' => now(),
        ]);

        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'treasurer']));
        $token = 'test-session-'.uniqid();
        $user->forceFill(['active_session_token' => $token])->save();

        $this->withSession(['active_session_token' => $token])
            ->actingAs($user)
            ->get(route('loans.show', $loan))
            ->assertOk()
            ->assertSee('Guarantors')
            ->assertSee('Declined');
    }
}
