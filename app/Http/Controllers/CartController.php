<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $cartService = new \App\Services\CartService();
        ['items' => $items, 'total' => $total] = $cartService->resolveCartItems();

        return view('cart.index', ['items' => $items, 'total' => $total]);
    }

    public function add(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Session::get('cart', []);
        $productId = $request->product_id;
        $quantity = $request->quantity;

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        Session::put('cart', $cart);

        if ($request->has('member_id')) {
            Session::put('cart_member_id', $request->member_id);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Product added to cart.']);
        }

        return back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = Session::get('cart', []);

        if ($request->quantity <= 0) {
            unset($cart[$request->product_id]);
        } else {
            $cart[$request->product_id] = $request->quantity;
        }

        Session::put('cart', $cart);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        $cart = Session::get('cart', []);
        unset($cart[$request->product_id]);
        Session::put('cart', $cart);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(): \Illuminate\Http\RedirectResponse
    {
        Session::forget('cart');

        return back()->with('success', 'Cart cleared.');
    }

    public function checkout(): \Illuminate\View\View
    {
        $cartService = new \App\Services\CartService();
        ['items' => $items, 'total' => $total] = $cartService->resolveCartItems();

        $members = \App\Models\Member::where('status', 'active')->orderBy('first_name')->get();
        $memberId = Session::get('cart_member_id');

        return view('cart.checkout', ['items' => $items, 'total' => $total, 'members' => $members, 'memberId' => $memberId]);
    }

    public function processCheckout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'payment_type' => 'required|in:cash,hire_purchase',
            'monthly_repayment' => 'required_if:payment_type,hire_purchase|nullable|numeric|min:0',
        ]);

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return back()->withErrors(['error' => 'Your cart is empty.']);
        }

        $member = \App\Models\Member::findOrFail($request->member_id);
        $cartService = new \App\Services\CartService();

        try {
            $orders = $cartService->processCheckout($cart, $member->id, $request->payment_type, $request->monthly_repayment);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('products.orders.show', $orders[0]->order_group ?? 'unknown')
            ->with('success', count($orders) . ' item(s) ordered successfully.');
    }
}
