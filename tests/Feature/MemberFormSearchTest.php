<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberFormSearchTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function adminUser(): User
    {
        if ($this->admin) {
            return $this->admin;
        }

        $token = 'test-session-'.uniqid();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-form-search-'.uniqid().'@naptin.coop',
            'password' => Hash::make('password'),
        ]);
        $this->admin->forceFill(['active_session_token' => $token])->save();

        return $this->admin;
    }

    private function makeMember(string $first, string $last, string $staffId, array $extra = []): Member
    {
        $region = Region::create([
            'name' => 'Region '.substr(uniqid(), -4),
            'code' => 'RC-'.substr(uniqid(), -4),
            'state' => 'S',
            'enabled' => true,
        ]);

        $member = Member::create(array_merge([
            'region_id' => $region->id,
            'staff_id' => $staffId,
            'first_name' => $first,
            'last_name' => $last,
            'status' => 'active',
            'monthly_salary' => 100000,
        ], $extra));

        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV/T/'.substr(uniqid(), -6),
            'balance' => 2500,
        ]);
        ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 5,
            'total_value' => 500,
            'share_price' => 100,
        ]);

        return $member;
    }

    private function searchJson(string $query)
    {
        return $this
            ->withSession(['active_session_token' => $this->adminUser()->active_session_token])
            ->actingAs($this->adminUser())
            ->getJson('/members/search/form?q='.urlencode($query));
    }

    public function test_form_search_returns_active_members_with_account_data(): void
    {
        $member = $this->makeMember('Jane', 'Doe', 'STAFF-99');

        $response = $this->searchJson('Jane');

        $response->assertStatus(200);
        $response->assertJsonCount(1);

        $result = $response->json()[0];
        $this->assertEquals($member->id, $result['id']);
        $this->assertEquals('Jane', $result['first_name']);
        $this->assertEquals('STAFF-99', $result['staff_id']);
        $this->assertEquals(2500, $result['balance']);
        $this->assertEquals(5, $result['shares']);
        $this->assertNotEquals('', $result['account_number']);
    }

    public function test_form_search_excludes_inactive_members(): void
    {
        $this->makeMember('Active', 'User', 'ACT-1');
        $this->makeMember('Retired', 'User', 'RET-1', ['status' => 'retired']);

        $response = $this->searchJson('User');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
        $this->assertEquals('Active', $response->json()[0]['first_name']);
    }

    public function test_form_search_returns_empty_array_when_no_match(): void
    {
        $response = $this->searchJson('zzzznomatch');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }
}
