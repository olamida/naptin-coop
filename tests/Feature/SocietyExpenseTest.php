<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\Member;
use App\Models\Product;
use App\Models\Region;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SocietyExpenseTest extends TestCase
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

    private function member(): Member
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR',
            'state' => 'FCT',
            'enabled' => true,
        ]);

        return Member::create([
            'region_id' => $region->id,
            'staff_id' => 'EXP001',
            'first_name' => 'Expense',
            'last_name' => 'Member',
            'status' => 'active',
        ]);
    }

    private function product(): Product
    {
        return Product::create([
            'name' => 'Executive Desk',
            'unit_price' => 50000,
            'stock_quantity' => 10,
            'enabled' => true,
        ]);
    }

    public function test_create_order_as_society_expense_posts_expense_journal_entry(): void
    {
        [$admin, $token] = $this->admin();
        $member = $this->member();
        $product = $this->product();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.orders.store'), [
                'member_id' => $member->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'payment_type' => 'cash',
                'is_society_expense' => '1',
            ])
            ->assertRedirect(route('products.orders'));

        $order = $member->purchaseOrders()->firstOrFail();

        $this->assertTrue($order->is_society_expense);
        $this->assertEquals(100000, $order->total_amount);
        $this->assertSame('approved', $order->status);
        $this->assertEquals(8, $product->fresh()->stock_quantity);

        $entry = JournalEntry::where('reference_type', 'purchase')->where('reference_id', $order->id)->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $this->assertSame('posted', $entry->status);

        $expenseLine = $lines->firstWhere('account.code', '5001');
        $this->assertNotNull($expenseLine);
        $this->assertEquals(100000, $expenseLine->debit);

        $cashLine = $lines->firstWhere('account.code', '1001');
        $this->assertNotNull($cashLine);
        $this->assertEquals(100000, $cashLine->credit);

        $this->assertFalse($lines->contains(fn ($l) => $l->account->code === '4002'));
    }

    public function test_create_order_without_expense_flag_posts_sale_journal_entry(): void
    {
        [$admin, $token] = $this->admin();
        $member = $this->member();
        $product = $this->product();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.orders.store'), [
                'member_id' => $member->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'payment_type' => 'cash',
            ])
            ->assertRedirect(route('products.orders'));

        $order = $member->purchaseOrders()->firstOrFail();
        $this->assertFalse($order->is_society_expense);

        $entry = JournalEntry::where('reference_type', 'purchase')->where('reference_id', $order->id)->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $this->assertNotNull($lines->firstWhere('account.code', '1301'), 'Inventory should be credited at cost for a normal sale');
        $this->assertNull($lines->firstWhere('account.code', '5001'));
    }

    public function test_cart_checkout_as_society_expense_posts_expense_entry(): void
    {
        [$admin, $token] = $this->admin();
        $member = $this->member();
        $product = $this->product();

        $cartService = new CartService('admin', $admin->id);
        $cartService->add($product->id, 3);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('cart.process'), [
                'member_id' => $member->id,
                'payment_type' => 'cash',
                'is_society_expense' => '1',
            ])
            ->assertRedirect();

        $order = $member->purchaseOrders()->firstOrFail();
        $this->assertTrue($order->is_society_expense);
        $this->assertEquals(150000, $order->total_amount);

        $entry = JournalEntry::where('reference_type', 'purchase')->where('reference_id', $order->id)->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $this->assertNotNull($lines->firstWhere('account.code', '5001'));
        $this->assertNull($lines->firstWhere('account.code', '4002'));
    }

    public function test_cash_sale_with_cost_price_posts_inventory_and_margin(): void
    {
        [$admin, $token] = $this->admin();
        $member = $this->member();
        $product = Product::create([
            'name' => 'Padded Chair',
            'unit_price' => 50000,
            'cost_price' => 30000,
            'stock_quantity' => 5,
            'enabled' => true,
        ]);

        $cartService = new CartService('admin', $admin->id);
        $cartService->add($product->id, 1);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('cart.process'), [
                'member_id' => $member->id,
                'payment_type' => 'cash',
            ])
            ->assertRedirect();

        $order = $member->purchaseOrders()->firstOrFail();
        $entry = JournalEntry::where('reference_type', 'purchase')->where('reference_id', $order->id)->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $cash = $lines->firstWhere('account.code', '1001');
        $inventory = $lines->firstWhere('account.code', '1301');
        $margin = $lines->firstWhere('account.code', '4005');

        $this->assertEquals(50000, $cash->debit);
        $this->assertEquals(30000, $inventory->credit);
        $this->assertEquals(20000, $margin->credit);
        $this->assertNull($lines->firstWhere('account.code', '4002'));
    }

    public function test_cash_sale_without_cost_posts_full_amount_to_inventory(): void
    {
        [$admin, $token] = $this->admin();
        $member = $this->member();
        $product = $this->product(); // no cost_price → cost defaults to unit_price

        $cartService = new CartService('admin', $admin->id);
        $cartService->add($product->id, 1);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('cart.process'), [
                'member_id' => $member->id,
                'payment_type' => 'cash',
            ])
            ->assertRedirect();

        $order = $member->purchaseOrders()->firstOrFail();
        $entry = JournalEntry::where('reference_type', 'purchase')->where('reference_id', $order->id)->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $inventory = $lines->firstWhere('account.code', '1301');
        $this->assertEquals(50000, $inventory->credit);
        $this->assertNull($lines->firstWhere('account.code', '4005'));
    }
}
