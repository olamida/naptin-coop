<?php

namespace Tests\Feature;

use App\Actions\Payroll\CompileAndLockPayroll;
use App\Http\Controllers\PayrollController;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\PayrollArrear;
use App\Models\PayrollDeduction;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PayrollArrearTest extends TestCase
{
    use RefreshDatabase;

    private function makePayrollWithShortfall(float $expected = 30000, float $actual = 10000): array
    {
        $region = Region::create(['name' => 'R', 'code' => 'R1', 'state' => 'S', 'enabled' => true]);
        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'S-'.substr(uniqid(), -6),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'status' => 'active',
            'monthly_salary' => 100000,
        ]);

        $payroll = MonthlyPayroll::create([
            'payroll_number' => 'PAY/T/001',
            'month' => 'July',
            'year' => 2026,
            'month_number' => 7,
            'grand_total' => $expected,
            'member_count' => 1,
            'status' => 'deducted',
        ]);

        PayrollDeduction::create([
            'monthly_payroll_id' => $payroll->id,
            'member_id' => $member->id,
            'expected_savings' => $expected,
            'total_expected' => $expected,
            'actual_savings' => $actual,
            'total_actual' => $actual,
            'status' => 'pending',
        ]);

        return [$payroll, $member];
    }

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create());
    }

    public function test_store_arrear_records_open_arrear_with_shortfall(): void
    {
        [$payroll, $member] = $this->makePayrollWithShortfall();
        $this->actingAsAdmin();

        $request = Request::create('/payroll/'.$payroll->id.'/arrears', 'POST', [
            'member_id' => $member->id,
            'reason' => 'Salary dept shortfall',
        ]);

        app()->call([app(PayrollController::class), 'storeArrear'], [
            'request' => $request,
            'monthlyPayroll' => $payroll,
        ]);

        $this->assertDatabaseHas('payroll_arrears', [
            'monthly_payroll_id' => $payroll->id,
            'member_id' => $member->id,
            'shortfall' => '20000.00',
            'status' => 'open',
            'reason' => 'Salary dept shortfall',
        ]);
    }

    public function test_store_arrear_rejects_when_no_shortfall_exists(): void
    {
        [$payroll, $member] = $this->makePayrollWithShortfall(30000, 30000);
        $this->actingAsAdmin();

        $request = Request::create('/payroll/'.$payroll->id.'/arrears', 'POST', [
            'member_id' => $member->id,
        ]);

        app()->call([app(PayrollController::class), 'storeArrear'], [
            'request' => $request,
            'monthlyPayroll' => $payroll,
        ]);

        $this->assertDatabaseCount('payroll_arrears', 0);
    }

    public function test_store_arrear_rejects_duplicate_open_arrear(): void
    {
        [$payroll, $member] = $this->makePayrollWithShortfall();
        $this->actingAsAdmin();

        PayrollArrear::create([
            'monthly_payroll_id' => $payroll->id,
            'member_id' => $member->id,
            'shortfall' => 20000,
            'status' => 'open',
        ]);

        $request = Request::create('/payroll/'.$payroll->id.'/arrears', 'POST', [
            'member_id' => $member->id,
        ]);

        app()->call([app(PayrollController::class), 'storeArrear'], [
            'request' => $request,
            'monthlyPayroll' => $payroll,
        ]);

        $this->assertDatabaseCount('payroll_arrears', 1);
    }

    public function test_bulk_store_creates_arrears_for_each_shortfall_member(): void
    {
        $region = Region::create(['name' => 'R', 'code' => 'R1', 'state' => 'S', 'enabled' => true]);
        $payroll = MonthlyPayroll::create([
            'payroll_number' => 'PAY/T/002',
            'month' => 'July',
            'year' => 2026,
            'month_number' => 7,
            'grand_total' => 60000,
            'member_count' => 2,
            'status' => 'deducted',
        ]);

        foreach ([10000, 20000] as $i => $actual) {
            $member = Member::create([
                'region_id' => $region->id,
                'staff_id' => 'S-'.substr(uniqid(), -6),
                'first_name' => 'Member'.$i,
                'last_name' => 'One',
                'status' => 'active',
            ]);
            PayrollDeduction::create([
                'monthly_payroll_id' => $payroll->id,
                'member_id' => $member->id,
                'expected_savings' => 30000,
                'total_expected' => 30000,
                'actual_savings' => $actual,
                'total_actual' => $actual,
                'status' => 'pending',
            ]);
        }

        $this->actingAsAdmin();
        $request = Request::create('/payroll/'.$payroll->id.'/arrears/bulk', 'POST');

        app()->call([app(PayrollController::class), 'storeAllArrears'], [
            'request' => $request,
            'monthlyPayroll' => $payroll,
        ]);

        $this->assertSame(2, PayrollArrear::where('monthly_payroll_id', $payroll->id)->count());
    }

    public function test_settle_arrear_marks_status_and_timestamp(): void
    {
        [$payroll, $member] = $this->makePayrollWithShortfall();
        $this->actingAsAdmin();

        $arrear = PayrollArrear::create([
            'monthly_payroll_id' => $payroll->id,
            'member_id' => $member->id,
            'shortfall' => 20000,
            'status' => 'open',
        ]);

        app()->call([app(PayrollController::class), 'settleArrear'], ['payrollArrear' => $arrear]);

        $this->assertSame('settled', $arrear->fresh()->status);
        $this->assertNotNull($arrear->fresh()->settled_at);
    }

    public function test_compile_carries_open_arrears_into_next_payroll(): void
    {
        $region = Region::create(['name' => 'R', 'code' => 'R1', 'state' => 'S', 'enabled' => true]);
        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'S-'.substr(uniqid(), -6),
            'first_name' => 'Carry',
            'last_name' => 'Forward',
            'status' => 'active',
            'monthly_salary' => 100000,
        ]);

        $prior = MonthlyPayroll::create([
            'payroll_number' => 'PAY/T/003',
            'month' => 'July',
            'year' => 2026,
            'month_number' => 7,
            'grand_total' => 30000,
            'member_count' => 1,
            'status' => 'deducted',
        ]);

        PayrollDeduction::create([
            'monthly_payroll_id' => $prior->id,
            'member_id' => $member->id,
            'expected_savings' => 30000,
            'total_expected' => 30000,
            'actual_savings' => 10000,
            'total_actual' => 10000,
            'status' => 'pending',
        ]);

        PayrollArrear::create([
            'monthly_payroll_id' => $prior->id,
            'member_id' => $member->id,
            'expected_amount' => 30000,
            'actual_amount' => 10000,
            'shortfall' => 20000,
            'status' => 'open',
        ]);

        $this->actingAsAdmin();
        $request = Request::create('/payroll/compile', 'POST', ['year' => 2026, 'month_number' => 8]);

        app()->call([app(PayrollController::class), 'compilePost'], ['request' => $request]);

        $payroll = MonthlyPayroll::where('year', 2026)->where('month_number', 8)->first();
        $this->assertNotNull($payroll);
        $this->assertSame('20000.00', $payroll->fresh()->total_arrears);

        $deduction = $payroll->deductions()->where('member_id', $member->id)->first();
        $this->assertNotNull($deduction);
        $this->assertSame('20000.00', $deduction->expected_arrears);
        $this->assertSame('35000.00', $deduction->total_expected);
        $this->assertSame('35000.00', $payroll->fresh()->grand_total);
    }

    public function test_compile_and_lock_carries_open_arrears(): void
    {
        $region = Region::create(['name' => 'R', 'code' => 'R1', 'state' => 'S', 'enabled' => true]);
        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'S-'.substr(uniqid(), -6),
            'first_name' => 'Locked',
            'last_name' => 'Carry',
            'status' => 'active',
            'monthly_salary' => 100000,
        ]);

        $prior = MonthlyPayroll::create([
            'payroll_number' => 'PAY/T/004',
            'month' => 'July',
            'year' => 2026,
            'month_number' => 7,
            'grand_total' => 30000,
            'member_count' => 1,
            'status' => 'deducted',
        ]);

        PayrollDeduction::create([
            'monthly_payroll_id' => $prior->id,
            'member_id' => $member->id,
            'expected_savings' => 30000,
            'total_expected' => 30000,
            'actual_savings' => 10000,
            'total_actual' => 10000,
            'status' => 'pending',
        ]);

        PayrollArrear::create([
            'monthly_payroll_id' => $prior->id,
            'member_id' => $member->id,
            'expected_amount' => 30000,
            'actual_amount' => 10000,
            'shortfall' => 20000,
            'status' => 'open',
        ]);

        $payroll = CompileAndLockPayroll::run(2026, 8);

        $this->assertSame('20000.00', $payroll->total_arrears);
        $deduction = $payroll->deductions()->where('member_id', $member->id)->first();
        $this->assertSame('20000.00', $deduction->expected_arrears);
        $this->assertSame('20000.00', $deduction->actual_arrears);
    }
}
