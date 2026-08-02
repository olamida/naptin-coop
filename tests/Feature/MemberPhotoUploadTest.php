<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super-admin']));
        $token = 'test-session-'.uniqid();
        $admin->forceFill(['active_session_token' => $token])->save();

        return [$admin, $token];
    }

    private function region(): Region
    {
        return Region::create([
            'name' => 'Test Region',
            'code' => 'TR',
            'state' => 'FCT',
            'enabled' => true,
        ]);
    }

    public function test_photo_upload_stores_file_and_links_it_to_member(): void
    {
        Storage::fake('public');

        [$admin, $token] = $this->admin();
        $region = $this->region();

        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('members.store'), [
                'region_id' => $region->id,
                'staff_id' => '999901',
                'first_name' => 'Test',
                'last_name' => 'Photo',
                'email' => '',
                'phone' => '',
                'gender' => 'male',
                'date_of_birth' => '',
                'employment_date' => '',
                'retirement_date' => '',
                'address' => '',
                'state_of_origin' => '',
                'nin' => '',
                'grade_level' => '',
                'monthly_salary' => '0',
                'monthly_savings' => '0',
                'status' => 'active',
                'photo' => $file,
            ])
            ->assertRedirect(route('members.show', ['member' => Member::where('staff_id', '999901')->first()]));

        $member = Member::where('staff_id', '999901')->firstOrFail();

        $this->assertNotNull($member->photo_path);
        $this->assertTrue(Storage::disk('public')->exists($member->photo_path));
        $this->assertStringStartsWith('member-photos/', $member->photo_path);
    }

    public function test_photo_upload_without_photo_leaves_photo_path_null(): void
    {
        Storage::fake('public');

        [$admin, $token] = $this->admin();
        $region = $this->region();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('members.store'), [
                'region_id' => $region->id,
                'staff_id' => '999902',
                'first_name' => 'No',
                'last_name' => 'Photo',
                'email' => '',
                'phone' => '',
                'gender' => 'female',
                'status' => 'active',
            ])
            ->assertRedirect();

        $member = Member::where('staff_id', '999902')->firstOrFail();
        $this->assertNull($member->photo_path);
    }

    public function test_photo_update_replaces_old_file(): void
    {
        Storage::fake('public');

        [$admin, $token] = $this->admin();
        $region = $this->region();

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => '999903',
            'first_name' => 'Old',
            'last_name' => 'Photo',
            'status' => 'active',
            'photo_path' => 'member-photos/old.jpg',
        ]);

        Storage::disk('public')->put('member-photos/old.jpg', 'old');
        $newFile = UploadedFile::fake()->image('new.jpg', 100, 100);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->put(route('members.update', $member), [
                'region_id' => $region->id,
                'staff_id' => '999903',
                'first_name' => 'Old',
                'last_name' => 'Photo',
                'email' => '',
                'phone' => '',
                'gender' => 'male',
                'status' => 'active',
                'photo' => $newFile,
            ])
            ->assertRedirect(route('members.show', $member));

        $member->refresh();

        $this->assertNotNull($member->photo_path);
        $this->assertNotEquals('member-photos/old.jpg', $member->photo_path);
        $this->assertTrue(Storage::disk('public')->exists($member->photo_path));
        $this->assertFalse(Storage::disk('public')->exists('member-photos/old.jpg'));
    }

    public function test_photo_removal_deletes_file(): void
    {
        Storage::fake('public');

        [$admin, $token] = $this->admin();
        $region = $this->region();

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => '999904',
            'first_name' => 'Remove',
            'last_name' => 'Photo',
            'status' => 'active',
            'photo_path' => 'member-photos/remove.jpg',
        ]);

        Storage::disk('public')->put('member-photos/remove.jpg', 'x');

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->put(route('members.update', $member), [
                'region_id' => $region->id,
                'staff_id' => '999904',
                'first_name' => 'Remove',
                'last_name' => 'Photo',
                'email' => '',
                'phone' => '',
                'gender' => 'male',
                'status' => 'active',
                'remove_photo' => '1',
            ])
            ->assertRedirect(route('members.show', $member));

        $member->refresh();

        $this->assertNull($member->photo_path);
        $this->assertFalse(Storage::disk('public')->exists('member-photos/remove.jpg'));
    }
}
