<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\LedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    public const TTL_DAYS = 3;

    public function __construct(
        public string $actorType = 'admin',
        public ?int $userId = null,
        public ?int $memberId = null,
    ) {}

    /**
     * Fetch or lazily create the cart for the current actor, refreshing expiry and pruning stale carts.
     */
    public function getCart(): Cart
    {
        $this->pruneExpired();

        $query = Cart::where('actor_type', $this->actorType)
            ->where('user_id', $this->userId);

        if ($this->memberId === null) {
            $query->whereNull('member_id');
        } else {
            $query->where('member_id', $this->memberId);
        }

        $cart = $query->first();

        if (! $cart) {
            $cart = Cart::create([
                'actor_type' => $this->actorType,
                'user_id' => $this->userId,
                'member_id' => $this->memberId,
                'items' => [],
                'expires_at' => now()->addDays(self::TTL_DAYS),
            ]);
        }

        $cart->update(['expires_at' => now()->addDays(self::TTL_DAYS)]);

        return $cart;
    }

    /**
     * Resolve cart items into structured products, quantities, and subtotals.
     */
    public function resolveCartItems(): array
    {
        $cart = $this->getCart();
        $items = [];
        $total = 0;

        foreach ($cart->items as $productId => $quantity) {
            $product = Product::find($productId);
            if ($product) {
                $subtotal = round($product->unit_price * $quantity, 2);
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
                $total += $subtotal;
            }
        }

        return [
            'items' => $items,
            'total' => round($total, 2),
            'cart_count' => count($cart->items),
            'cart_quantity' => array_sum($cart->items),
        ];
    }

    public function add(int $productId, int $quantity): array
    {
        $cart = $this->getCart();
        $items = $cart->items;
        $key = (string) $productId;
        $items[$key] = ($items[$key] ?? 0) + $quantity;
        $cart->update(['items' => $items]);

        return $this->counts($cart);
    }

    public function update(int $productId, int $quantity): array
    {
        $cart = $this->getCart();
        $items = $cart->items;

        if ($quantity <= 0) {
            unset($items[(string) $productId]);
        } else {
            $items[(string) $productId] = $quantity;
        }

        $cart->update(['items' => $items]);

        return $this->counts($cart);
    }

    public function remove(int $productId): array
    {
        $cart = $this->getCart();
        $items = $cart->items;
        unset($items[(string) $productId]);
        $cart->update(['items' => $items]);

        return $this->counts($cart);
    }

    public function clear(): void
    {
        $cart = $this->getCart();
        $cart->update(['items' => []]);
    }

    public function counts(?Cart $cart = null): array
    {
        $cart ??= $this->getCart();

        return [
            'cart_count' => count($cart->items),
            'cart_quantity' => array_sum($cart->items),
        ];
    }

    public function isEmpty(): bool
    {
        return empty($this->getCart()->items);
    }

    /**
     * Generate a unique order number atomically.
     */
    public function generateOrderNumber(): string
    {
        $year = date('Y');
        $prefix = 'PO/' . $year . '/';

        return DB::transaction(function () use ($prefix) {
            $last = PurchaseOrder::withTrashed()
                ->where('order_number', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING(order_number, -6) AS UNSIGNED) DESC')
                ->value('order_number');

            $next = $last ? ((int) substr($last, -6)) + 1 : 1;

            return $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Process checkout: create orders and decrement stock within a transaction, then empty the cart.
     *
     * @throws \Exception
     */
    public function processCheckout(int $orderMemberId, string $paymentType, ?float $monthlyRepayment = null): array
    {
        $cart = $this->getCart();
        $cartItems = $cart->items;

        if (empty($cartItems)) {
            throw new \Exception('Your cart is empty.');
        }

        $orderGroup = 'GRP-' . date('Y') . '-' . strtoupper(Str::random(8));
        $orders = [];

        DB::transaction(function () use ($cart, $cartItems, $orderMemberId, $paymentType, $monthlyRepayment, $orderGroup, &$orders) {
            $products = Product::whereIn('id', array_keys($cartItems))->lockForUpdate()->get()->keyBy('id');

            foreach ($cartItems as $productId => $quantity) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    throw new \Exception("Product ID {$productId} not found.");
                }

                if ($quantity > $product->stock_quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}");
                }

                $orderNumber = $this->generateOrderNumber();
                $totalAmount = round($quantity * $product->unit_price, 2);
                $status = $paymentType === 'cash' ? 'approved' : 'pending';

                $order = PurchaseOrder::create([
                    'order_number' => $orderNumber,
                    'order_group' => $orderGroup,
                    'member_id' => $orderMemberId,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->unit_price,
                    'total_amount' => $totalAmount,
                    'payment_type' => $paymentType,
                    'monthly_repayment' => $monthlyRepayment ?? 0,
                    'status' => $status,
                ]);

                $product->decrement('stock_quantity', $quantity);

                $ledger = app(LedgerService::class);
                if ($paymentType === 'cash') {
                    $ledger->postCashSale($order->id, $totalAmount);
                } else {
                    $ledger->postHirePurchaseSale($order->id, $totalAmount);
                }

                $orders[] = $order;
            }

            $cart->update(['items' => [], 'expires_at' => now()->addDays(self::TTL_DAYS)]);
        });

        return $orders;
    }

    /**
     * Delete expired carts.
     */
    public function pruneExpired(): void
    {
        Cart::where('expires_at', '<', now())->delete();
    }
}
