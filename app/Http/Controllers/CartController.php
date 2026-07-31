<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function cartService(): CartService
    {
        return new CartService('admin', Auth::id());
    }

    public function index(): \Illuminate\View\View
    {
        ['items' => $items, 'total' => $total] = $this->cartService()->resolveCartItems();

        return view('cart.index', ['items' => $items, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $this->cartService()->add($request->product_id, $request->quantity);

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

        $this->cartService()->update($request->product_id, $request->quantity);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $this->cartService()->remove($request->product_id);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(): \Illuminate\Http\RedirectResponse
    {
        $this->cartService()->clear();

        return back()->with('success', 'Cart cleared.');
    }

    public function checkout(): \Illuminate\View\View
    {
        ['items' => $items, 'total' => $total] = $this->cartService()->resolveCartItems();

        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        $memberOptions = $members->map(fn ($m) => [
            'id' => $m->id,
            'first_name' => $m->first_name,
            'last_name' => $m->last_name,
            'staff_id' => $m->staff_id,
            'staff_id_display' => $m->staff_id_display,
        ])->values();

        return view('cart.checkout', ['items' => $items, 'total' => $total, 'members' => $members, 'memberOptions' => $memberOptions]);
    }

    public function processCheckout(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'payment_type' => 'required|in:cash,hire_purchase',
            'monthly_repayment' => 'required_if:payment_type,hire_purchase|nullable|numeric|min:0',
        ]);

        try {
            $orders = $this->cartService()->processCheckout($request->member_id, $request->payment_type, $request->monthly_repayment);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('products.orders.show', $orders[0]->order_group ?? 'unknown')
            ->with('success', count($orders) . ' item(s) ordered successfully.');
    }
}
