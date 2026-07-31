<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Region;
use App\Models\ShareAccount;
use App\Models\SavingsAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalDashboardVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_portal_dashboard_loads(): void
    {
        $region = Region::create(['name' => 'R', 'code' => 'R1', 'state' => 'S', 'enabled' => true]);
        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'V-' . substr(uniqid(), -6),
            'first_name' => 'Portal',
            'last_name' => 'User',
            'status' => 'active',
            'monthly_salary' => 100000,
        ]);
        $user = User::create([
            'name' => 'Portal User',
            'email' => 'portal-verify@naptin.coop',
            'password' => Hash::make('password'),
            'member_id' => $member->id,
        ]);
        SavingsAccount::create([
            'member_id' => $member->id,
            'account_number' => 'SAV/V/' . substr(uniqid(), -6),
            'balance' => 0,
        ]);
        ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 0,
            'total_value' => 0,
            'share_price' => 100,
        ]);

        $token = 'test-session-' . uniqid();
        $user->forceFill(['active_session_token' => $token])->save();

        $response = $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($user)
            ->get('/my');

        if ($response->getStatusCode() !== 200) {
            fwrite(STDERR, "REDIRECT TO: " . $response->headers->get('Location') . "\n");
        }

        $response->assertStatus(200);
        $response->assertSee('Savings', false);
        $response->assertSee('Shares', false);
    }
}
