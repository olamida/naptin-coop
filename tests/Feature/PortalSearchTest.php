<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\ShareAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalSearchTest extends TestCase
{
    use RefreshDatabase;

    private function portalMember(string $staffId): array
    {
        $region = Region::create([
            'name' => 'R',
            'code' => 'R'.substr(uniqid(), -6),
            'state' => 'S',
            'enabled' => true,
        ]);
        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => $staffId,
            'first_name' => 'Portal',
            'last_name' => 'User',
            'status' => 'active',
            'monthly_salary' => 100000,
        ]);
        $user = User::create([
            'name' => 'Portal User',
            'email' => 'portal-'.strtolower($staffId).'@naptin.coop',
            'password' => Hash::make('password'),
            'member_id' => $member->id,
        ]);
        $savings = SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV/'.strtoupper($staffId),
            'balance' => 0,
        ]);
        $shares = ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 0,
            'total_value' => 0,
            'share_price' => 100,
        ]);
        $token = 'test-session-'.uniqid();
        $user->forceFill(['active_session_token' => $token])->save();

        return compact('member', 'user', 'savings', 'shares', 'token');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/my/search')->assertRedirect(route('login'));
    }

    public function test_empty_query_returns_quick_actions(): void
    {
        $ctx = $this->portalMember('A1');

        $response = $this
            ->withSession(['active_session_token' => $ctx['token']])
            ->actingAs($ctx['user'])
            ->getJson('/my/search');

        $data = $response->assertOk()->json();

        $this->assertSame('actions', $data[0]['key']);
        $this->assertSame('Quick Actions', $data[0]['label']);
        $this->assertNotEmpty($data[0]['items']);
    }

    public function test_loan_search_is_scoped_to_the_members_own_loans(): void
    {
        $ctxA = $this->portalMember('A1');
        $ctxB = $this->portalMember('B2');

        $loanA = Loan::create([
            'member_id' => $ctxA['member']->id,
            'loan_number' => 'LOAN/A/0001',
            'type' => 'regular',
            'amount' => 100000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 8750,
            'total_repaid' => 0,
            'outstanding' => 105000,
            'application_date' => now(),
            'status' => 'pending',
        ]);
        Loan::create([
            'member_id' => $ctxB['member']->id,
            'loan_number' => 'LOAN/B/0001',
            'type' => 'regular',
            'amount' => 50000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 4375,
            'total_repaid' => 0,
            'outstanding' => 52500,
            'application_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this
            ->withSession(['active_session_token' => $ctxA['token']])
            ->actingAs($ctxA['user'])
            ->getJson('/my/search?q=LOAN');

        $data = $response->assertOk()->json();

        $this->assertCount(1, $data);
        $this->assertSame('loans', $data[0]['key']);
        $this->assertCount(1, $data[0]['items']);
        $this->assertSame($loanA->loan_number, $data[0]['items'][0]['name']);
        $this->assertSame(route('portal.loan-detail', $loanA), $data[0]['items'][0]['url']);

        $response = $this
            ->withSession(['active_session_token' => $ctxA['token']])
            ->actingAs($ctxA['user'])
            ->getJson('/my/search?q=LOAN%2FB');

        $this->assertSame([], $response->assertOk()->json());
    }

    public function test_savings_search_is_scoped_to_the_members_own_account(): void
    {
        $ctxA = $this->portalMember('A1');
        $ctxB = $this->portalMember('B2');

        SavingsTransaction::create([
            'savings_account_id' => $ctxA['savings']->id,
            'reference' => 'SAV/DEP/A000001',
            'type' => 'deposit',
            'amount' => 5000,
            'balance_before' => 0,
            'balance_after' => 5000,
            'status' => 'completed',
            'source' => 'test',
            'transaction_date' => now(),
        ]);
        SavingsTransaction::create([
            'savings_account_id' => $ctxB['savings']->id,
            'reference' => 'SAV/DEP/B000001',
            'type' => 'deposit',
            'amount' => 9000,
            'balance_before' => 0,
            'balance_after' => 9000,
            'status' => 'completed',
            'source' => 'test',
            'transaction_date' => now(),
        ]);

        $response = $this
            ->withSession(['active_session_token' => $ctxA['token']])
            ->actingAs($ctxA['user'])
            ->getJson('/my/search?q=SAV%2F');

        $data = $response->assertOk()->json();

        $this->assertCount(1, $data);
        $this->assertSame('savings', $data[0]['key']);
        $this->assertCount(1, $data[0]['items']);
        $this->assertSame('SAV/DEP/A000001', $data[0]['items'][0]['name']);
    }
}
