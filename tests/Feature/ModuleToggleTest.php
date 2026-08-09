<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModuleToggleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $token = 'test-session-'.uniqid();
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-module-'.uniqid().'@naptin.coop',
            'password' => Hash::make('password'),
        ]);
        $user->forceFill(['active_session_token' => $token])->save();

        return $user;
    }

    private function sessionData(User $user): array
    {
        return ['active_session_token' => $user->active_session_token];
    }

    public function test_toggle_persists_when_module_disabled(): void
    {
        $user = $this->admin();
        Company::instance()->update(['shares_enabled' => false, 'dividends_enabled' => false]);

        $this->assertFalse(Company::instance()->moduleEnabled('shares'));
        $this->assertFalse(Company::instance()->moduleEnabled('dividends'));
    }

    public function test_shares_routes_redirect_when_module_disabled(): void
    {
        $user = $this->admin();
        Company::instance()->update(['shares_enabled' => false]);

        $this->withSession($this->sessionData($user))
            ->actingAs($user)
            ->get(route('shares.index'))
            ->assertRedirect(route('dashboard'));

        $this->withSession($this->sessionData($user))
            ->actingAs($user)
            ->get(route('shares.purchase'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_dividend_routes_redirect_when_module_disabled(): void
    {
        $user = $this->admin();
        Company::instance()->update(['dividends_enabled' => false]);

        $this->withSession($this->sessionData($user))
            ->actingAs($user)
            ->get(route('dividends.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_shares_routes_work_when_module_enabled(): void
    {
        $user = $this->admin();
        Company::instance()->update(['shares_enabled' => true]);

        $this->withSession($this->sessionData($user))
            ->actingAs($user)
            ->get(route('shares.index'))
            ->assertOk();
    }

    public function test_settings_toggle_saves_disabled_state(): void
    {
        $user = $this->admin();
        Company::instance()->update(['shares_enabled' => true, 'dividends_enabled' => true]);

        $this->withSession($this->sessionData($user))
            ->actingAs($user)
            ->put(route('admin.settings.update'), [
                'name' => Company::instance()->name,
                'thrift_amount' => 1000,
                'membership_fee' => 500,
                'savings_interest_rate' => 3,
                'loan_interest_rate' => 5,
                'max_loan_multiplier' => 3,
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $company = Company::instance()->fresh();
        $this->assertFalse($company->moduleEnabled('shares'));
        $this->assertFalse($company->moduleEnabled('dividends'));
    }

    public function test_settings_toggle_saves_enabled_state(): void
    {
        $user = $this->admin();
        Company::instance()->update(['shares_enabled' => false, 'dividends_enabled' => false]);

        $this->withSession($this->sessionData($user))
            ->actingAs($user)
            ->put(route('admin.settings.update'), [
                'name' => Company::instance()->name,
                'thrift_amount' => 1000,
                'membership_fee' => 500,
                'savings_interest_rate' => 3,
                'loan_interest_rate' => 5,
                'max_loan_multiplier' => 3,
                'shares_enabled' => 1,
                'dividends_enabled' => 1,
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $company = Company::instance()->fresh();
        $this->assertTrue($company->moduleEnabled('shares'));
        $this->assertTrue($company->moduleEnabled('dividends'));
    }
}
