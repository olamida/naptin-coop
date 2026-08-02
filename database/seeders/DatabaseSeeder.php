<?php

namespace Database\Seeders;

use App\Models\LoanProduct;
use App\Models\Member;
use App\Models\Position;
use App\Models\Product;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionsSeeder::class);
        $regions = [
            ['name' => 'Abuja', 'code' => 'ABJ', 'state' => 'FCT'],
            ['name' => 'Lagos', 'code' => 'LAG', 'state' => 'Lagos'],
            ['name' => 'Port Harcourt', 'code' => 'PHC', 'state' => 'Rivers'],
            ['name' => 'Kano', 'code' => 'KAN', 'state' => 'Kano'],
            ['name' => 'Enugu', 'code' => 'ENU', 'state' => 'Enugu'],
            ['name' => 'Jos', 'code' => 'JOS', 'state' => 'Plateau'],
            ['name' => 'Ibadan', 'code' => 'IBD', 'state' => 'Oyo'],
            ['name' => 'Kaduna', 'code' => 'KAD', 'state' => 'Kaduna'],
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(['code' => $region['code']], $region);
        }

        $positions = [
            ['name' => 'Chairman', 'slug' => 'chairman', 'is_executive' => true],
            ['name' => 'Vice Chairman', 'slug' => 'vice-chairman', 'is_executive' => true],
            ['name' => 'Secretary', 'slug' => 'secretary', 'is_executive' => true],
            ['name' => 'Assistant Secretary', 'slug' => 'assistant-secretary', 'is_executive' => true],
            ['name' => 'Treasurer', 'slug' => 'treasurer', 'is_executive' => true],
            ['name' => 'Financial Secretary', 'slug' => 'financial-secretary', 'is_executive' => true],
            ['name' => 'PRO', 'slug' => 'pro', 'is_executive' => true],
            ['name' => 'Auditor', 'slug' => 'auditor', 'is_executive' => true],
            ['name' => 'Welfare Secretary', 'slug' => 'welfare-secretary', 'is_executive' => true],
            ['name' => 'Loan Officer', 'slug' => 'loan-officer', 'is_executive' => false],
            ['name' => 'Teller', 'slug' => 'teller', 'is_executive' => false],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(['slug' => $position['slug']], $position);
        }

        $roles = ['super-admin', 'admin', 'secretary', 'treasurer', 'loan-officer', 'teller', 'member'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@naptin.coop'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        $membersData = [
            [
                'first_name' => 'John',
                'last_name' => 'Adebayo',
                'email' => 'john.adebayo@naptin.coop',
                'phone' => '08012345678',
                'gender' => 'male',
                'monthly_salary' => 250000,
            ],
            [
                'first_name' => 'Fatima',
                'last_name' => 'Ibrahim',
                'email' => 'fatima.ibrahim@naptin.coop',
                'phone' => '08023456789',
                'gender' => 'female',
                'monthly_salary' => 300000,
            ],
            [
                'first_name' => 'Emeka',
                'last_name' => 'Okonkwo',
                'email' => 'emeka.okonkwo@naptin.coop',
                'phone' => '08034567890',
                'gender' => 'male',
                'monthly_salary' => 200000,
            ],
            [
                'first_name' => 'Amina',
                'last_name' => 'Bello',
                'email' => 'amina.bello@naptin.coop',
                'phone' => '08045678901',
                'gender' => 'female',
                'monthly_salary' => 350000,
            ],
            [
                'first_name' => 'Olusegun',
                'last_name' => 'Adesanya',
                'email' => 'olusegun.adesanya@naptin.coop',
                'phone' => '08056789012',
                'gender' => 'male',
                'monthly_salary' => 275000,
            ],
        ];

        $regionIds = Region::pluck('id')->toArray();

        foreach ($membersData as $index => $data) {
            $staffId = 'NAPTIN/'.str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $member = Member::firstOrCreate(
                ['staff_id' => $staffId],
                [
                    ...$data,
                    'region_id' => $regionIds[array_rand($regionIds)],
                    'date_of_birth' => now()->subYears(30 + rand(0, 15))->subDays(rand(0, 365)),
                    'employment_date' => now()->subYears(rand(2, 10)),
                    'grade_level' => 'GL'.rand(7, 15),
                    'status' => 'active',
                    'address' => '123 Cooperative Road, Abuja',
                    'state_of_origin' => 'FCT',
                ]
            );

            SavingsAccount::firstOrCreate(
                ['member_id' => $member->id],
                [
                    'account_number' => 'SAV/'.Str::upper(Str::random(2)).'/'.str_pad($member->id, 6, '0', STR_PAD_LEFT),
                    'balance' => rand(50000, 500000),
                ]
            );

            ShareAccount::firstOrCreate(
                ['member_id' => $member->id],
                [
                    'total_shares' => rand(5, 50),
                    'total_value' => rand(500, 5000),
                    'share_price' => 100.00,
                ]
            );

            // Create user account for each member so they can log in
            $userEmail = $data['email'];
            $memberUser = User::firstOrCreate(
                ['email' => $userEmail],
                [
                    'name' => $data['first_name'].' '.$data['last_name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'member_id' => $member->id,
                ]
            );
            if (! $memberUser->hasRole('member')) {
                $memberUser->assignRole('member');
            }
            // Link user back to member record
            if (! $member->user_id) {
                $member->update(['user_id' => $memberUser->id]);
            }
        }

        // Loan Products
        $loanProducts = [
            [
                'name' => 'Regular Loan',
                'slug' => 'regular',
                'description' => 'Standard cooperative loan for members with at least 6 months membership.',
                'min_amount' => 50000,
                'max_amount' => 2000000,
                'interest_rate' => 5.00,
                'repayment_method' => 'flat',
                'max_term_months' => 24,
                'processing_fee_pct' => 1.00,
                'requires_guarantors' => true,
                'requires_collateral' => false,
                'enabled' => true,
            ],
            [
                'name' => 'Emergency Loan',
                'slug' => 'emergency',
                'description' => 'Quick emergency loan for urgent needs. Higher interest, shorter tenure.',
                'min_amount' => 20000,
                'max_amount' => 500000,
                'interest_rate' => 3.00,
                'repayment_method' => 'flat',
                'max_term_months' => 6,
                'processing_fee_pct' => 0.50,
                'requires_guarantors' => false,
                'requires_collateral' => false,
                'enabled' => true,
            ],
            [
                'name' => 'Educational Loan',
                'slug' => 'educational',
                'description' => 'Loan for educational expenses — school fees, training, certifications.',
                'min_amount' => 100000,
                'max_amount' => 1000000,
                'interest_rate' => 2.50,
                'repayment_method' => 'reducing_balance',
                'max_term_months' => 12,
                'processing_fee_pct' => 0.50,
                'requires_guarantors' => true,
                'requires_collateral' => false,
                'enabled' => true,
            ],
            [
                'name' => 'Special Loan',
                'slug' => 'special',
                'description' => 'Special purpose loan with custom terms approved by EXCO.',
                'min_amount' => 200000,
                'max_amount' => 5000000,
                'interest_rate' => 7.50,
                'repayment_method' => 'reducing_balance',
                'max_term_months' => 36,
                'processing_fee_pct' => 2.00,
                'requires_guarantors' => true,
                'requires_collateral' => true,
                'enabled' => true,
            ],
        ];

        foreach ($loanProducts as $product) {
            LoanProduct::firstOrCreate(['slug' => $product['slug']], $product);
        }

        // Products
        $products = [
            ['name' => 'Laptop - HP EliteBook', 'description' => 'Refurbished HP EliteBook laptop for personal use.', 'unit_price' => 350000, 'stock_quantity' => 10],
            ['name' => 'Generator - Tiger 5KVA', 'description' => 'Tiger brand 5KVA petrol generator.', 'unit_price' => 280000, 'stock_quantity' => 5],
            ['name' => 'Solar Panel Kit', 'description' => 'Complete solar panel kit with inverter and batteries.', 'unit_price' => 750000, 'stock_quantity' => 3],
            ['name' => 'Office Chair', 'description' => 'Ergonomic office chair with adjustable height.', 'unit_price' => 85000, 'stock_quantity' => 20],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['name' => $product['name']], $product);
        }

        $demoMember = Member::where('staff_id', 'NAPTIN/0001')->first();
        if ($demoMember) {
            $memberUser = User::firstOrCreate(
                ['email' => 'member@naptin.coop'],
                [
                    'name' => $demoMember->first_name.' '.$demoMember->last_name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'member_id' => $demoMember->id,
                ]
            );
            if (! $memberUser->hasRole('member')) {
                $memberUser->assignRole('member');
            }
        }

        $this->call(DemoDataSeeder::class);
    }
}
