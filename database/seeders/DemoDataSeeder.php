<?php

namespace Database\Seeders;

use App\Enums\GuarantorStatus;
use App\Models\ActivityLog;
use App\Models\Dividend;
use App\Models\DividendDistribution;
use App\Models\Loan;
use App\Models\LoanApprovalLog;
use App\Models\LoanGuarantor;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\MonthlyPayroll;
use App\Models\PayrollDeduction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@naptin.coop')->first();
        $regions = Region::all();
        $regularProduct = LoanProduct::where('slug', 'regular')->first();
        $emergencyProduct = LoanProduct::where('slug', 'emergency')->first();
        $educationalProduct = LoanProduct::where('slug', 'educational')->first();
        $specialProduct = LoanProduct::where('slug', 'special')->first();
        $products = Product::all();
        $regionIds = $regions->pluck('id')->toArray();

        if (Member::count() >= 20) {
            return;
        }

        $membersData = [
            ['first_name' => 'Chukwuemeka', 'last_name' => 'Okafor', 'email' => 'chukwuemeka.okafor@naptin.coop', 'phone' => '08061234567', 'gender' => 'male', 'monthly_salary' => 280000],
            ['first_name' => 'Ngozi', 'last_name' => 'Adeyemi', 'email' => 'ngozi.adeyemi@naptin.coop', 'phone' => '08072345678', 'gender' => 'female', 'monthly_salary' => 320000],
            ['first_name' => 'Abdullahi', 'last_name' => 'Mohammed', 'email' => 'abdullahi.mohammed@naptin.coop', 'phone' => '08083456789', 'gender' => 'male', 'monthly_salary' => 190000],
            ['first_name' => 'Blessing', 'last_name' => 'Effiong', 'email' => 'blessing.effiong@naptin.coop', 'phone' => '08094567890', 'gender' => 'female', 'monthly_salary' => 450000],
            ['first_name' => 'Tunde', 'last_name' => 'Lawal', 'email' => 'tunde.lawal@naptin.coop', 'phone' => '08105678901', 'gender' => 'male', 'monthly_salary' => 220000],
            ['first_name' => 'Aisha', 'last_name' => 'Abubakar', 'email' => 'aisha.abubakar@naptin.coop', 'phone' => '08116789012', 'gender' => 'female', 'monthly_salary' => 380000],
            ['first_name' => 'Obinna', 'last_name' => 'Nwosu', 'email' => 'obinna.nwosu@naptin.coop', 'phone' => '08127890123', 'gender' => 'male', 'monthly_salary' => 175000],
            ['first_name' => 'Halima', 'last_name' => 'Yusuf', 'email' => 'halima.yusuf@naptin.coop', 'phone' => '08138901234', 'gender' => 'female', 'monthly_salary' => 340000],
            ['first_name' => 'Kayode', 'last_name' => 'Ogunleye', 'email' => 'kayode.ogunleye@naptin.coop', 'phone' => '08149012345', 'gender' => 'male', 'monthly_salary' => 260000],
            ['first_name' => 'Funke', 'last_name' => 'Akindele', 'email' => 'funke.akindele@naptin.coop', 'phone' => '08150123456', 'gender' => 'female', 'monthly_salary' => 290000],
            ['first_name' => 'Ibrahim', 'last_name' => 'Danjuma', 'email' => 'ibrahim.danjuma@naptin.coop', 'phone' => '08161234567', 'gender' => 'male', 'monthly_salary' => 500000],
            ['first_name' => 'Chidinma', 'last_name' => 'Eze', 'email' => 'chidinma.eze@naptin.coop', 'phone' => '08172345678', 'gender' => 'female', 'monthly_salary' => 160000],
            ['first_name' => 'Yakubu', 'last_name' => 'Garba', 'email' => 'yakubu.garba@naptin.coop', 'phone' => '08183456789', 'gender' => 'male', 'monthly_salary' => 310000],
            ['first_name' => 'Sade', 'last_name' => 'Oyinbo', 'email' => 'sade.oyinbo@naptin.coop', 'phone' => '08194567890', 'gender' => 'female', 'monthly_salary' => 240000],
            ['first_name' => 'Musa', 'last_name' => 'Bello', 'email' => 'musa.bello@naptin.coop', 'phone' => '08205678901', 'gender' => 'male', 'monthly_salary' => 150000],
        ];

        $createdMembers = [];
        $statuses = ['active', 'active', 'active', 'active', 'active', 'active', 'active', 'active', 'active', 'active', 'active', 'active', 'active', 'inactive', 'active'];

        foreach ($membersData as $index => $data) {
            $staffId = 'NAPTIN/' . str_pad(6 + $index, 4, '0', STR_PAD_LEFT);
            $member = Member::firstOrCreate(
                ['staff_id' => $staffId],
                [
                    ...$data,
                    'region_id' => $regionIds[array_rand($regionIds)],
                    'date_of_birth' => now()->subYears(28 + rand(0, 18))->subDays(rand(0, 365)),
                    'employment_date' => now()->subYears(rand(1, 12)),
                    'grade_level' => 'GL' . rand(7, 15),
                    'status' => $statuses[$index] ?? 'active',
                    'address' => (100 + $index) . ' Cooperative Avenue, Abuja',
                    'state_of_origin' => ['FCT', 'Lagos', 'Rivers', 'Kano', 'Enugu', 'Oyo', 'Kaduna', 'Plateau'][array_rand(['FCT', 'Lagos', 'Rivers', 'Kano', 'Enugu', 'Oyo', 'Kaduna', 'Plateau'])],
                ]
            );

            SavingsAccount::firstOrCreate(
                ['member_id' => $member->id],
                [
                    'account_number' => 'SAV/' . strtoupper(Str::random(2)) . '/' . str_pad($member->id, 6, '0', STR_PAD_LEFT),
                    'balance' => rand(50000, 600000),
                ]
            );

            $shareCount = rand(5, 80);
            ShareAccount::firstOrCreate(
                ['member_id' => $member->id],
                [
                    'total_shares' => $shareCount,
                    'total_value' => $shareCount * 100.00,
                    'share_price' => 100.00,
                ]
            );

            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'member_id' => $member->id,
                ]
            )->assignRole('member');

            if (!$member->user_id) {
                $member->update(['user_id' => User::where('email', $data['email'])->first()->id]);
            }

            $createdMembers[] = $member;
        }

        $allMembers = Member::all()->shuffle();
        $memberIds = $allMembers->pluck('id')->toArray();

        $loanConfigs = [
            ['product' => $regularProduct, 'amount' => 500000, 'tenure' => 12, 'rate' => 5.0, 'status' => 'pending'],
            ['product' => $emergencyProduct, 'amount' => 150000, 'tenure' => 3, 'rate' => 3.0, 'status' => 'pending'],
            ['product' => $regularProduct, 'amount' => 800000, 'tenure' => 18, 'rate' => 5.0, 'status' => 'pending'],
            ['product' => $educationalProduct, 'amount' => 300000, 'tenure' => 6, 'rate' => 2.5, 'status' => 'approved'],
            ['product' => $regularProduct, 'amount' => 1000000, 'tenure' => 24, 'rate' => 5.0, 'status' => 'approved'],
            ['product' => $emergencyProduct, 'amount' => 200000, 'tenure' => 4, 'rate' => 3.0, 'status' => 'approved'],
            ['product' => $regularProduct, 'amount' => 600000, 'tenure' => 12, 'rate' => 5.0, 'status' => 'repaying', 'repayments' => 4],
            ['product' => $educationalProduct, 'amount' => 400000, 'tenure' => 8, 'rate' => 2.5, 'status' => 'repaying', 'repayments' => 3],
            ['product' => $regularProduct, 'amount' => 750000, 'tenure' => 15, 'rate' => 5.0, 'status' => 'repaying', 'repayments' => 6],
            ['product' => $emergencyProduct, 'amount' => 100000, 'tenure' => 3, 'rate' => 3.0, 'status' => 'repaying', 'repayments' => 2],
            ['product' => $regularProduct, 'amount' => 450000, 'tenure' => 10, 'rate' => 5.0, 'status' => 'repaying', 'repayments' => 5],
            ['product' => $regularProduct, 'amount' => 300000, 'tenure' => 6, 'rate' => 5.0, 'status' => 'completed', 'repayments' => 6],
            ['product' => $emergencyProduct, 'amount' => 180000, 'tenure' => 4, 'rate' => 3.0, 'status' => 'completed', 'repayments' => 4],
            ['product' => $regularProduct, 'amount' => 900000, 'tenure' => 18, 'rate' => 5.0, 'status' => 'completed', 'repayments' => 18],
            ['product' => $educationalProduct, 'amount' => 250000, 'tenure' => 6, 'rate' => 2.5, 'status' => 'completed', 'repayments' => 6],
            ['product' => $regularProduct, 'amount' => 550000, 'tenure' => 12, 'rate' => 5.0, 'status' => 'completed', 'repayments' => 12],
            ['product' => $regularProduct, 'amount' => 650000, 'tenure' => 12, 'rate' => 5.0, 'status' => 'rejected'],
            ['product' => $specialProduct, 'amount' => 2000000, 'tenure' => 24, 'rate' => 7.5, 'status' => 'rejected'],
            ['product' => $regularProduct, 'amount' => 800000, 'tenure' => 18, 'rate' => 5.0, 'status' => 'defaulted'],
            ['product' => $emergencyProduct, 'amount' => 350000, 'tenure' => 6, 'rate' => 3.0, 'status' => 'defaulted'],
        ];

        foreach ($loanConfigs as $i => $config) {
            $memberId = $memberIds[$i % count($memberIds)];
            $loanNumber = 'REG/2026/' . str_pad($i + 1, 6, '0', STR_PAD_LEFT);

            if (Loan::where('loan_number', $loanNumber)->exists()) {
                continue;
            }

            $monthlyRepayment = ($config['amount'] * (1 + $config['rate'] / 100)) / $config['tenure'];

            $loan = Loan::create([
                'member_id' => $memberId,
                'loan_product_id' => $config['product']->id,
                'loan_number' => $loanNumber,
                'type' => $config['product']->slug,
                'amount' => $config['amount'],
                'interest_rate' => $config['rate'],
                'tenure_months' => $config['tenure'],
                'monthly_repayment' => round($monthlyRepayment, 2),
                'total_repaid' => 0,
                'outstanding' => $config['amount'],
                'application_date' => now()->subDays(rand(30, 180)),
                'status' => $config['status'],
                'purpose' => ['School fees payment', 'Medical emergency', 'Home renovation', 'Business expansion', 'Car maintenance', 'Family event'][array_rand(['School fees payment', 'Medical emergency', 'Home renovation', 'Business expansion', 'Car maintenance', 'Family event'])],
                'approved_by' => $admin?->id,
            ]);

            if ($config['status'] !== 'pending') {
                $loan->update(['approval_date' => $loan->application_date->addDays(rand(1, 7))]);
            }

            if (in_array($config['status'], ['approved', 'repaying', 'completed', 'defaulted'])) {
                $loan->update([
                    'disbursement_date' => $loan->approval_date?->addDays(rand(1, 5)),
                    'maturity_date' => $loan->disbursement_date?->addMonths($config['tenure']),
                ]);
            }

            if (in_array($config['status'], ['repaying', 'completed'])) {
                $totalRepaid = 0;
                $outstanding = $config['amount'];
                for ($r = 0; $r < $config['repayments']; $r++) {
                    $paymentAmount = round($monthlyRepayment, 2);
                    $interestPortion = round($outstanding * ($config['rate'] / 100 / 12), 2);
                    $principalPortion = round($paymentAmount - $interestPortion, 2);
                    if ($principalPortion < 0) {
                        $principalPortion = $paymentAmount;
                        $interestPortion = 0;
                    }
                    $outstanding = max(0, $outstanding - $principalPortion);
                    $totalRepaid += $paymentAmount;

                    LoanRepayment::create([
                        'loan_id' => $loan->id,
                        'member_id' => $memberId,
                        'reference' => 'LPY/' . strtoupper(Str::random(2)) . '/' . str_pad($r + 1, 4, '0', STR_PAD_LEFT),
                        'amount' => $paymentAmount,
                        'principal_portion' => $principalPortion,
                        'interest_portion' => $interestPortion,
                        'outstanding_after' => $outstanding,
                        'payment_method' => 'salary_deduction',
                        'payment_date' => $loan->disbursement_date?->addMonths($r + 1) ?? now()->subMonths($config['repayments'] - $r),
                    ]);
                }

                $loan->update([
                    'total_repaid' => round($totalRepaid, 2),
                    'outstanding' => round($outstanding, 2),
                ]);
            }

            if ($config['status'] === 'completed') {
                $loan->update(['outstanding' => 0, 'total_repaid' => $config['amount'] * (1 + $config['rate'] / 100)]);
            }

            if ($config['status'] === 'rejected') {
                $loan->update([
                    'rejection_reason' => 'Application does not meet current eligibility criteria.',
                ]);
                LoanApprovalLog::create([
                    'loan_id' => $loan->id,
                    'user_id' => $admin?->id,
                    'action' => 'rejected',
                    'old_status' => 'pending',
                    'new_status' => 'rejected',
                    'notes' => 'Does not meet eligibility criteria.',
                ]);
            }

            if ($config['status'] === 'defaulted') {
                $loan->update([
                    'total_repaid' => $config['amount'] * 0.3,
                    'outstanding' => $config['amount'] * 0.7,
                ]);
            }

            if (in_array($config['status'], ['approved', 'repaying', 'completed']) && $config['product']->requires_guarantors) {
                $guarantorMembers = $allMembers->where('id', '!=', $memberId)->random(min(2, $allMembers->count() - 1));
                foreach ($guarantorMembers as $gm) {
                    LoanGuarantor::create([
                        'loan_id' => $loan->id,
                        'member_id' => $gm->id,
                        'status' => GuarantorStatus::Accepted,
                        'responded_at' => now()->subDays(rand(1, 5)),
                    ]);
                }
            }

            LoanApprovalLog::create([
                'loan_id' => $loan->id,
                'user_id' => $admin?->id,
                'action' => 'submitted',
                'old_status' => null,
                'new_status' => 'pending',
            ]);

            if ($config['status'] !== 'pending' && $config['status'] !== 'rejected') {
                LoanApprovalLog::create([
                    'loan_id' => $loan->id,
                    'user_id' => $admin?->id,
                    'action' => 'approved',
                    'old_status' => 'pending',
                    'new_status' => 'approved',
                ]);
            }

            if (in_array($config['status'], ['repaying', 'completed', 'defaulted'])) {
                LoanApprovalLog::create([
                    'loan_id' => $loan->id,
                    'user_id' => $admin?->id,
                    'action' => 'disbursed',
                    'old_status' => 'approved',
                    'new_status' => 'disbursed',
                ]);
            }
        }

        $savingsAccounts = SavingsAccount::with('member')->get();
        $txnCount = 0;
        foreach ($savingsAccounts as $account) {
            if ($account->transactions()->count() >= 2) {
                continue;
            }

            $balance = $account->balance;
            for ($t = 0; $t < rand(2, 4); $t++) {
                $isDeposit = $t % 3 !== 2;
                $amount = rand(5000, 50000);
                $type = $isDeposit ? 'deposit' : 'withdrawal';

                if ($type === 'withdrawal' && $balance < $amount) {
                    continue;
                }

                $balanceBefore = $balance;
                $balance = $isDeposit ? $balance + $amount : $balance - $amount;

                SavingsTransaction::create([
                    'savings_account_id' => $account->id,
                    'reference' => ($isDeposit ? 'SAV/DEP/' : 'SAV/WTH/') . strtoupper(Str::random(2)) . '/' . str_pad(++$txnCount, 4, '0', STR_PAD_LEFT),
                    'type' => $type,
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balance,
                    'status' => 'completed',
                    'transaction_date' => now()->subDays(rand(10, 180)),
                    'approved_by' => $admin?->id,
                    'approved_at' => now()->subDays(rand(5, 170)),
                ]);
            }

            $account->update(['balance' => $balance]);
        }

        $shareAccounts = ShareAccount::with('member')->get();
        $shTxnCount = 0;
        foreach ($shareAccounts as $account) {
            if ($account->transactions()->count() >= 2) {
                continue;
            }

            $totalShares = 0;
            $totalValue = 0;
            for ($s = 0; $s < rand(1, 3); $s++) {
                $shares = rand(2, 15);
                $amount = $shares * 100;
                $totalShares += $shares;
                $totalValue += $amount;

                ShareTransaction::create([
                    'share_account_id' => $account->id,
                    'reference' => 'SHR/PUR/' . strtoupper(Str::random(2)) . '/' . str_pad(++$shTxnCount, 4, '0', STR_PAD_LEFT),
                    'type' => 'purchase',
                    'shares' => $shares,
                    'amount' => $amount,
                    'balance_after' => $totalValue,
                    'status' => 'completed',
                    'transaction_date' => now()->subDays(rand(15, 90)),
                ]);
            }

            $account->update([
                'total_shares' => $totalShares,
                'total_value' => $totalValue,
            ]);
        }

        $orderNumber = 1;
        foreach ($allMembers->random(min(8, $allMembers->count())) as $member) {
            if (PurchaseOrder::where('member_id', $member->id)->exists()) {
                continue;
            }

            $product = $products->random();
            $qty = rand(1, 2);
            $isHirePurchase = rand(1, 4) === 1;
            $statuses = ['pending', 'approved', 'active', 'completed'];
            $poStatus = $statuses[array_rand($statuses)];

            $totalAmount = $product->unit_price * $qty;
            PurchaseOrder::create([
                'order_number' => 'PO/2026/' . str_pad($orderNumber++, 6, '0', STR_PAD_LEFT),
                'order_group' => (string) Str::uuid(),
                'member_id' => $member->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $product->unit_price,
                'total_amount' => $totalAmount,
                'payment_type' => $isHirePurchase ? 'hire_purchase' : 'cash',
                'monthly_repayment' => $isHirePurchase ? round($totalAmount / 6, 2) : 0,
                'amount_paid' => $poStatus === 'completed' ? $totalAmount : ($isHirePurchase ? round($totalAmount * 0.3, 2) : $totalAmount),
                'status' => $poStatus,
                'collected_at' => $poStatus !== 'pending' ? now()->subDays(rand(1, 30)) : null,
                'approved_by' => $poStatus !== 'pending' ? $admin?->id : null,
            ]);
        }

        $totalSharesAll = ShareAccount::sum('total_shares');
        if ($totalSharesAll > 0 && !Dividend::where('year', 2025)->exists()) {
            $totalProfit = 5000000;
            $dividend = Dividend::create([
                'dividend_number' => 'DIV/2025/' . str_pad(1, 4, '0', STR_PAD_LEFT),
                'year' => 2025,
                'total_profit' => $totalProfit,
                'total_distributed' => $totalProfit,
                'eligible_members' => ShareAccount::where('total_shares', '>', 0)->count(),
                'status' => 'completed',
                'approved_by' => $admin?->id,
            ]);

            foreach (ShareAccount::where('total_shares', '>', 0)->get() as $sa) {
                $amount = $totalSharesAll > 0 ? round(($sa->total_shares / $totalSharesAll) * $totalProfit, 2) : 0;
                DividendDistribution::create([
                    'dividend_id' => $dividend->id,
                    'member_id' => $sa->member_id,
                    'share_count' => $sa->total_shares,
                    'amount' => $amount,
                    'status' => 'paid',
                    'paid_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        if (!MonthlyPayroll::where('year', 2026)->where('month_number', 6)->exists()) {
            $activeMembers = Member::where('status', 'active')->with(['savingsAccount', 'loans' => fn($q) => $q->whereIn('status', ['disbursed', 'repaying'])->limit(1)])->get();
            $totalSavings = 0;
            $totalLoans = 0;
            $totalShares = 0;
            $grandTotal = 0;

            $payroll = MonthlyPayroll::create([
                'payroll_number' => 'PAY/2026/001',
                'month' => 'June',
                'year' => 2026,
                'month_number' => 6,
                'total_savings' => 0,
                'total_loan_repayments' => 0,
                'total_share_contributions' => 0,
                'total_purchases' => 0,
                'grand_total' => 0,
                'member_count' => $activeMembers->count(),
                'status' => 'completed',
            ]);

            foreach ($activeMembers as $member) {
                $expectedSavings = round(($member->monthly_salary ?? 200000) * 0.10, 2);
                $expectedShares = round(($member->monthly_salary ?? 200000) * 0.05, 2);
                $activeLoan = $member->loans->first();
                $expectedLoan = $activeLoan && in_array($activeLoan->status, ['disbursed', 'repaying']) ? $activeLoan->monthly_repayment : 0;
                $totalExpected = $expectedSavings + $expectedShares + $expectedLoan;

                PayrollDeduction::create([
                    'monthly_payroll_id' => $payroll->id,
                    'member_id' => $member->id,
                    'expected_savings' => $expectedSavings,
                    'expected_loan_repayment' => $expectedLoan,
                    'expected_share_contribution' => $expectedShares,
                    'expected_purchase' => 0,
                    'total_expected' => $totalExpected,
                    'actual_savings' => $expectedSavings,
                    'actual_loan_repayment' => $expectedLoan,
                    'actual_share_contribution' => $expectedShares,
                    'actual_purchase' => 0,
                    'total_actual' => $totalExpected,
                    'status' => 'completed',
                ]);

                $totalSavings += $expectedSavings;
                $totalLoans += $expectedLoan;
                $totalShares += $expectedShares;
                $grandTotal += $totalExpected;
            }

            $payroll->update([
                'total_savings' => $totalSavings,
                'total_loan_repayments' => $totalLoans,
                'total_share_contributions' => $totalShares,
                'grand_total' => $grandTotal,
            ]);
        }

        ActivityLog::insert([
            ['user_id' => $admin?->id, 'event' => 'member.registered', 'description' => 'New member registered: NAPTIN/0006', 'properties' => json_encode(['member_id' => 6]), 'created_at' => now()->subDays(45)],
            ['user_id' => $admin?->id, 'event' => 'loan.approved', 'description' => 'Loan REG/2026/0007 approved for disbursal', 'properties' => json_encode(['loan_id' => 7]), 'created_at' => now()->subDays(30)],
            ['user_id' => $admin?->id, 'event' => 'savings.deposit', 'description' => 'Deposit of ₦50,000 recorded for SAV account', 'properties' => json_encode(['amount' => 50000]), 'created_at' => now()->subDays(20)],
            ['user_id' => $admin?->id, 'event' => 'share.purchased', 'description' => '10 shares purchased for NAPTIN/0003', 'properties' => json_encode(['shares' => 10, 'amount' => 1000]), 'created_at' => now()->subDays(15)],
            ['user_id' => $admin?->id, 'event' => 'loan.disbursed', 'description' => 'Loan REG/2026/0009 disbursed — ₦750,000', 'properties' => json_encode(['loan_id' => 9, 'amount' => 750000]), 'created_at' => now()->subDays(10)],
            ['user_id' => $admin?->id, 'event' => 'dividend.declared', 'description' => '2025 dividend declared — ₦5,000,000 total', 'properties' => json_encode(['dividend_id' => 1, 'total' => 5000000]), 'created_at' => now()->subDays(5)],
            ['user_id' => $admin?->id, 'event' => 'payroll.completed', 'description' => 'June 2026 payroll processed successfully', 'properties' => json_encode(['payroll_id' => 1]), 'created_at' => now()->subDays(3)],
        ]);
    }
}
