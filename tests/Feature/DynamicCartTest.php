<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DynamicCartTest extends TestCase
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

    private function product(string $name = 'Test Product', int $stock = 10): Product
    {
        return Product::create([
            'name' => $name,
            'unit_price' => 2500,
            'stock_quantity' => $stock,
            'enabled' => true,
        ]);
    }

    public function test_add_returns_json_with_counts(): void
    {
        [$admin, $token] = $this->admin();
        $product = $this->product();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'cart_count' => 1,
                'cart_quantity' => 2,
            ]);
    }

    public function test_update_returns_json_with_counts(): void
    {
        [$admin, $token] = $this->admin();
        $product = $this->product();

        (new CartService('admin', $admin->id))->add($product->id, 1);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->postJson(route('cart.update'), ['product_id' => $product->id, 'quantity' => 5])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'cart_count' => 1,
                'cart_quantity' => 5,
            ]);
    }

    public function test_remove_and_clear_return_json(): void
    {
        [$admin, $token] = $this->admin();
        $productA = $this->product('Product A');
        $productB = $this->product('Product B');
        $service = new CartService('admin', $admin->id);
        $service->add($productA->id, 1);
        $service->add($productB->id, 1);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->postJson(route('cart.remove'), ['product_id' => $productA->id])
            ->assertOk()
            ->assertJson(['success' => true, 'cart_count' => 1]);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->postJson(route('cart.clear'))
            ->assertOk()
            ->assertJson(['success' => true, 'cart_count' => 0]);

        $this->assertTrue($service->isEmpty());
    }

    public function test_cart_page_renders_db_backed_items(): void
    {
        [$admin, $token] = $this->admin();
        $product = $this->product('Executive Laptop');

        (new CartService('admin', $admin->id))->add($product->id, 1);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Executive Laptop')
            ->assertSee('cart-badge');
    }

    public function test_empty_cart_page_renders_empty_state(): void
    {
        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Your cart is empty.');
    }
}
