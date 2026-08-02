<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
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

    private function product(int $stock = 10): Product
    {
        return Product::create([
            'name' => 'Office Chair',
            'unit_price' => 25000,
            'stock_quantity' => $stock,
            'enabled' => true,
        ]);
    }

    public function test_admin_can_adjust_stock_up(): void
    {
        [$admin, $token] = $this->admin();
        $product = $this->product();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.adjust-stock', $product), [
                'adjustment' => 5,
                'reason' => 'New delivery',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(15, $product->fresh()->stock_quantity);
    }

    public function test_admin_can_adjust_stock_down(): void
    {
        [$admin, $token] = $this->admin();
        $product = $this->product();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.adjust-stock', $product), [
                'adjustment' => -4,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(6, $product->fresh()->stock_quantity);
    }

    public function test_stock_cannot_go_below_zero(): void
    {
        [$admin, $token] = $this->admin();
        $product = $this->product(3);

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->post(route('products.adjust-stock', $product), [
                'adjustment' => -5,
            ])
            ->assertSessionHasErrors('error');

        $this->assertEquals(3, $product->fresh()->stock_quantity);
    }

    public function test_standalone_stock_page_redirects_to_products(): void
    {
        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('admin.stock'))
            ->assertRedirect(route('products.index'));
    }
}
