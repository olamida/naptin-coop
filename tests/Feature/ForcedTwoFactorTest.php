<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ForcedTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role, array $overrides = []): User
    {
        $user = User::factory()->create($overrides);
        $user->assignRole(Role::create(['name' => $role]));

        return $user;
    }

    public function test_forced_role_without_2fa_is_redirected_to_setup(): void
    {
        config(['security.enforce_two_factor_roles' => ['super-admin', 'admin', 'treasurer']]);

        $treasurer = $this->userWithRole('treasurer');

        $this->actingAs($treasurer)
            ->get(route('admin.data-import'))
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_super_admin_without_2fa_is_redirected_to_setup(): void
    {
        config(['security.enforce_two_factor_roles' => ['super-admin', 'admin', 'treasurer']]);

        $admin = $this->userWithRole('super-admin');

        $this->actingAs($admin)
            ->get(route('admin.data-import'))
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_forced_role_with_2fa_enabled_but_unverified_goes_to_challenge(): void
    {
        config(['security.enforce_two_factor_roles' => ['super-admin', 'admin', 'treasurer']]);

        $treasurer = $this->userWithRole('treasurer', ['totp_enabled' => true]);

        $this->actingAs($treasurer)
            ->get(route('admin.data-import'))
            ->assertRedirect(route('two-factor.challenge'));
    }

    public function test_non_forced_role_is_not_blocked(): void
    {
        config(['security.enforce_two_factor_roles' => ['super-admin', 'admin', 'treasurer']]);

        $secretary = $this->userWithRole('secretary');

        $this->actingAs($secretary)
            ->get(route('admin.data-import'))
            ->assertOk();
    }

    public function test_enforcement_is_disabled_by_default_in_tests(): void
    {
        config(['security.enforce_two_factor_roles' => []]);

        $treasurer = $this->userWithRole('treasurer');

        $this->actingAs($treasurer)
            ->get(route('admin.data-import'))
            ->assertOk();
    }

    public function test_verified_session_bypasses_challenge_for_forced_role(): void
    {
        config(['security.enforce_two_factor_roles' => ['super-admin', 'admin', 'treasurer']]);

        $treasurer = $this->userWithRole('treasurer', ['totp_enabled' => true]);

        $this
            ->withSession(['two_factor_verified' => true])
            ->actingAs($treasurer)
            ->get(route('admin.data-import'))
            ->assertOk();
    }
}
