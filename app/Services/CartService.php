<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Resolve cart session data into structured items with products, quantities, and subtotals.
     */
    public function resolveCartItems(): array
    {
        $cart = Session::get('cart', []);
        $items = [];
        $total = 0;

        if (empty($cart)) {
            return ['items' => [], 'total' => 0];
        }

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        foreach ($cart as $productId => $quantity) {
            $product = $products->get($productId);
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

        return ['items' => $items, 'total' => round($total, 2)];
    }

    /**
     * Generate a unique order number atomically.
     */
    public function generateOrderNumber(): string
    {
        $year = date('Y');
        $prefix = 'PO/' . $year . '/';

        return DB::transaction(function () use ($prefix, $year) {
            $count = PurchaseOrder::whereYear('created_at', $year)->lockForUpdate()->count() + 1;

            return $prefix . str_pad($count, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Process checkout: create orders and decrement stock within a transaction.
     *
     * @throws \Exception
     */
    public function processCheckout(array $cart, int $memberId, string $paymentType, ?float $monthlyRepayment = null): array
    {
        $orderGroup = 'GRP-' . date('Y') . '-' . strtoupper(Str::random(8));
        $orders = [];

        DB::transaction(function () use ($cart, $memberId, $paymentType, $monthlyRepayment, $orderGroup, &$orders) {
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product) {
                    throw new \Exception("Product ID {$productId} not found.");
                }

                if ($quantity > $product->stock_quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}");
                }

                $orderNumber = $this->generateOrderNumber();
                $totalAmount = round($quantity * $product->unit_price, 2);
                $status = $paymentType === 'cash' ? 'approved' : 'pending';

                $orders[] = PurchaseOrder::create([
                    'order_number' => $orderNumber,
                    'order_group' => $orderGroup,
                    'member_id' => $memberId,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->unit_price,
                    'total_amount' => $totalAmount,
                    'payment_type' => $paymentType,
                    'monthly_repayment' => $monthlyRepayment ?? 0,
                    'status' => $status,
                ]);

                $product->decrement('stock_quantity', $quantity);
            }
        });

        Session::forget('cart');
        Session::forget('cart_member_id');

        return $orders;
    }
}
