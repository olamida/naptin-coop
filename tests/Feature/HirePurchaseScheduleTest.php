<?php

namespace Tests\Feature;

use App\Models\HirePurchaseSchedule;
use App\Models\JournalEntry;
use App\Models\Member;
use App\Models\Product;
use App\Models\Region;
use App\Models\User;
use App\Services\CartService;
use App\Services\HirePurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HirePurchaseScheduleTest extends TestCase
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
            'staff_id' => 'HP'.substr(uniqid(), -6),
            'first_name' => 'Hire',
            'last_name' => 'Purchase',
            'status' => 'active',
        ]);
    }

    private function product(): Product
    {
        return Product::create([
            'name' => 'Sewing Machine',
            'unit_price' => 100000,
            'stock_quantity' => 5,
            'enabled' => true,
        ]);
    }

    public function test_creating_hire_purchase_order_generates_schedule(): void
    {
        [$admin] = $this->admin();
        $member = $this->member();
        $product = $this->product();

        $cartService = new CartService('admin', $admin->id);
        $cartService->add($product->id, 1);

        $orders = $cartService->processCheckout($member->id, 'hire_purchase', 40000);
        $order = $orders[0];

        $schedules = $order->schedules()->orderBy('installment_number')->get();

        $this->assertCount(3, $schedules);
        $this->assertEquals(40000, $schedules[0]->principal_amount);
        $this->assertEquals(40000, $schedules[1]->principal_amount);
        $this->assertEquals(20000, $schedules[2]->principal_amount);
        $this->assertEquals(0, $schedules[2]->balance_after);
        $this->assertSame('pending', $schedules[0]->status);
    }

    public function test_paying_an_instalment_marks_it_paid_and_posts_cash_journal(): void
    {
        [$admin] = $this->admin();
        $member = $this->member();
        $product = $this->product();

        $cartService = new CartService('admin', $admin->id);
        $cartService->add($product->id, 1);
        $order = $cartService->processCheckout($member->id, 'hire_purchase', 40000)[0];
        $order->update(['status' => 'active']);

        app(HirePurchaseService::class)->applyPayment($order, 40000);

        $first = HirePurchaseSchedule::where('purchase_order_id', $order->id)
            ->where('installment_number', 1)
            ->firstOrFail();

        $this->assertSame('paid', $first->status);
        $this->assertEquals(40000, $first->amount_paid);
        $this->assertEquals(40000, $order->fresh()->amount_paid);
        $this->assertSame('active', $order->fresh()->status);

        $entry = JournalEntry::where('reference_type', 'purchase')
            ->where('reference_id', $order->id)
            ->where('description', 'like', 'Hire purchase instalment%')
            ->firstOrFail();
        $lines = $entry->lines()->with('account')->get();

        $this->assertEquals(40000, $lines->firstWhere('account.code', '1001')->debit);
        $this->assertEquals(40000, $lines->firstWhere('account.code', '1201')->credit);
    }

    public function test_paying_all_instalments_completes_order(): void
    {
        [$admin] = $this->admin();
        $member = $this->member();
        $product = $this->product();

        $cartService = new CartService('admin', $admin->id);
        $cartService->add($product->id, 1);
        $order = $cartService->processCheckout($member->id, 'hire_purchase', 40000)[0];
        $order->update(['status' => 'active']);

        $service = app(HirePurchaseService::class);
        $service->applyPayment($order, 40000);
        $service->applyPayment($order, 40000);
        $service->applyPayment($order, 20000);

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertEquals(100000, $order->fresh()->amount_paid);
        $this->assertSame(0, $order->schedules()->where('status', '!=', 'paid')->count());
    }

    public function test_payment_exceeding_outstanding_balance_throws(): void
    {
        [$admin] = $this->admin();
        $member = $this->member();
        $product = $this->product();

        $cartService = new CartService('admin', $admin->id);
        $cartService->add($product->id, 1);
        $order = $cartService->processCheckout($member->id, 'hire_purchase', 40000)[0];

        $this->expectException(\RuntimeException::class);

        app(HirePurchaseService::class)->applyPayment($order, 150000);
    }

    public function test_record_payment_route_accepts_repayment(): void
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
                'payment_type' => 'hire_purchase',
                'monthly_repayment' => 40000,
            ])
            ->assertRedirect(route('products.orders'));

        $order = $member->purchaseOrders()->firstOrFail();
        $this->assertCount(3, $order->schedules);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.orders.approve', $order))
            ->assertRedirect();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.orders.collect', $order))
            ->assertRedirect();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.orders.payment', $order), ['amount' => 40000])
            ->assertRedirect();

        $first = HirePurchaseSchedule::where('purchase_order_id', $order->id)
            ->where('installment_number', 1)
            ->firstOrFail();
        $this->assertSame('paid', $first->status);
    }
}
