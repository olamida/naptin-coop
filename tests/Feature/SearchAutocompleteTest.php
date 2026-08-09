<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Region;
use App\Models\SavingsAccount;
use App\Models\ShareAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SearchAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function memberData(string $prefix): array
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.strtoupper(substr(uniqid(), -4)),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        return [
            'region_id' => $region->id,
            'staff_id' => $prefix.strtoupper(substr(uniqid(), -6)),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'monthly_salary' => 100000,
            'status' => 'active',
        ];
    }

    private function adminUser(): User
    {
        if ($this->admin) {
            return $this->admin;
        }

        $token = 'test-session-'.uniqid();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-search-'.uniqid().'@naptin.coop',
            'password' => Hash::make('password'),
        ]);
        $this->admin->forceFill(['active_session_token' => $token])->save();
        $this->admin->assignRole(Role::firstOrCreate(['name' => 'admin']));

        return $this->admin;
    }

    private function authedGet(string $url)
    {
        return $this
            ->withSession(['active_session_token' => $this->adminUser()->active_session_token])
            ->actingAs($this->adminUser())
            ->getJson($url);
    }

    public function test_product_search_returns_autocomplete_shape(): void
    {
        Product::create(['name' => 'Laptop HP', 'unit_price' => 250000, 'cost_price' => 220000, 'stock_quantity' => 5, 'enabled' => true]);

        $response = $this->authedGet(route('products.search').'?q=laptop');

        $response->assertOk();
        $this->assertCount(1, $response->json());
        $result = $response->json()[0];
        $this->assertSame('Laptop HP', $result['label']);
        $this->assertIsString($result['sublabel']);
        $this->assertArrayHasKey('url', $result);
    }

    public function test_public_shop_product_search_works_without_auth(): void
    {
        Product::create(['name' => 'Solar Panel', 'unit_price' => 150000, 'cost_price' => 120000, 'stock_quantity' => 3, 'enabled' => true]);

        $response = $this->getJson(route('shop.search').'?q=solar');

        $response->assertOk();
        $this->assertCount(1, $response->json());
        $this->assertSame('Solar Panel', $response->json()[0]['label']);
    }

    public function test_member_search_returns_label_and_sublabel(): void
    {
        Member::create($this->memberData('STF'));

        $response = $this->authedGet(route('members.search').'?q=jane');

        $response->assertOk();
        $result = $response->json()[0];
        $this->assertSame('Jane Doe', $result['label']);
        $this->assertIsString($result['url']);
    }

    public function test_loan_search_returns_loan_show_url(): void
    {
        $member = Member::create($this->memberData('STF'));
        $loan = Loan::create([
            'member_id' => $member->id,
            'loan_number' => 'LN-'.substr(uniqid(), -6),
            'type' => 'regular',
            'amount' => 50000,
            'interest_rate' => 5,
            'tenure_months' => 12,
            'monthly_repayment' => 4375,
            'outstanding' => 50000,
            'processing_fee' => 0,
            'application_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->authedGet(route('loans.search').'?q='.$loan->loan_number);

        $response->assertOk();
        $this->assertSame(route('loans.show', $loan), $response->json()[0]['url']);
    }

    public function test_savings_search_links_to_member_savings_detail(): void
    {
        $member = Member::create($this->memberData('STF'));
        SavingsAccount::create(['member_id' => $member->id, 'account_number' => 'SAV-'.uniqid(), 'balance' => 1000, 'status' => 'active']);

        $response = $this->authedGet(route('savings.search').'?q=jane');

        $response->assertOk();
        $this->assertSame(route('members.savings-detail', $member), $response->json()[0]['url']);
    }

    public function test_purchase_search_links_to_order_group(): void
    {
        $member = Member::create($this->memberData('STF'));
        $product = Product::create(['name' => 'Table Fan', 'unit_price' => 15000, 'cost_price' => 12000, 'stock_quantity' => 4, 'enabled' => true]);
        $group = 'ORD-'.strtoupper(substr(uniqid(), -6));
        PurchaseOrder::create([
            'order_group' => $group,
            'order_number' => $group.'-1',
            'member_id' => $member->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 15000,
            'total_amount' => 15000,
            'payment_type' => 'cash',
            'status' => 'approved',
        ]);

        $response = $this->authedGet(route('purchases.search').'?q='.$group);

        $response->assertOk();
        $this->assertSame(route('products.orders.show', $group), $response->json()[0]['url']);
    }

    public function test_share_search_links_to_member_show(): void
    {
        $member = Member::create($this->memberData('STF'));
        ShareAccount::create(['member_id' => $member->id, 'total_shares' => 10, 'total_value' => 1000, 'share_price' => 100, 'status' => 'active']);

        $response = $this->authedGet(route('shares.search').'?q=jane');

        $response->assertOk();
        $this->assertSame(route('members.show', $member), $response->json()[0]['url']);
    }
}
