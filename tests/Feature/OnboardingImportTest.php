<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OnboardingImportTest extends TestCase
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

    private function buildXlsx(string $path, array $members, array $savings = [], array $shares = []): void
    {
        $spreadsheet = new Spreadsheet;

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('members');
        $sheet->fromArray(
            ['staff_id', 'first_name', 'last_name', 'middle_name', 'region', 'email', 'phone', 'gender', 'date_of_birth', 'employment_date', 'address', 'state_of_origin', 'nin', 'grade_level', 'monthly_salary', 'status', 'external_reference'],
            null,
            'A1'
        );
        foreach ($members as $i => $row) {
            $sheet->fromArray(array_pad($row, 17, ''), null, 'A'.($i + 2));
        }

        if (! empty($savings)) {
            $savingsSheet = $spreadsheet->createSheet();
            $savingsSheet->setTitle('opening_savings');
            $savingsSheet->fromArray(['staff_id', 'amount', 'transaction_date', 'notes', 'external_reference'], null, 'A1');
            foreach ($savings as $i => $row) {
                $savingsSheet->fromArray(array_pad($row, 5, ''), null, 'A'.($i + 2));
            }
        }

        if (! empty($shares)) {
            $sharesSheet = $spreadsheet->createSheet();
            $sharesSheet->setTitle('shares');
            $sharesSheet->fromArray(['staff_id', 'shares', 'share_price', 'external_reference'], null, 'A1');
            foreach ($shares as $i => $row) {
                $sharesSheet->fromArray(array_pad($row, 4, ''), null, 'A'.($i + 2));
            }
        }

        (new Xlsx($spreadsheet))->save($path);
    }

    public function test_onboarding_import_accepts_minimal_members_with_defaults(): void
    {
        Region::create(['name' => 'Lagos', 'code' => 'LAG', 'state' => 'Lagos', 'enabled' => true]);

        $path = tempnam(sys_get_temp_dir(), 'onb').'.xlsx';
        $this->buildXlsx($path, [
            ['STF001', 'John', 'Doe', '', 'Lagos'],
            ['STF002', 'Jane', 'Smith', '', 'Lagos', '', '', 'female'],
        ]);

        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('admin.onboarding.import'), [
                'import_file' => new UploadedFile($path, 'onboarding.xlsx', null, null, true),
            ])
            ->assertRedirect(route('admin.onboarding'));

        $this->assertSame(2, Member::count());

        $john = Member::where('staff_id', 'STF001')->firstOrFail();
        $this->assertSame('John', $john->first_name);
        $this->assertSame('Doe', $john->last_name);
        $this->assertSame('active', $john->status);
        $this->assertEquals(0, $john->monthly_salary);
        $this->assertNotNull($john->savingsAccount);
        $this->assertNotNull($john->shareAccount);

        $jane = Member::where('staff_id', 'STF002')->firstOrFail();
        $this->assertSame('female', $jane->gender);
        $this->assertNull($jane->email);
        $this->assertNull($jane->phone);
    }

    public function test_onboarding_import_applies_opening_savings_and_shares(): void
    {
        Region::create(['name' => 'Abuja', 'code' => 'ABJ', 'state' => 'FCT', 'enabled' => true]);

        $path = tempnam(sys_get_temp_dir(), 'onb').'.xlsx';
        $this->buildXlsx($path, [
            ['STF003', 'Adamu', 'Bello', '', 'Abuja'],
        ], [
            ['STF003', '25000', '2026-01-05', 'Opening balance', ''],
        ], [
            ['STF003', '10', '100', ''],
        ]);

        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('admin.onboarding.import'), [
                'import_file' => new UploadedFile($path, 'onboarding.xlsx', null, null, true),
            ])
            ->assertRedirect(route('admin.onboarding'));

        $member = Member::where('staff_id', 'STF003')->firstOrFail();
        $this->assertEquals(25000, $member->savingsAccount->balance);
        $this->assertEquals(10, $member->shareAccount->total_shares);
    }

    protected function tearDown(): void
    {
        foreach (glob(sys_get_temp_dir().'/onb*.xlsx') ?: [] as $f) {
            @unlink($f);
        }

        parent::tearDown();
    }
}
