<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $products = Product::orderBy('name')->get();
        return view('admin.stock', ['products' => $products]);
    }

    public function update(Request $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $newQuantity = $product->stock_quantity + $validated['adjustment'];
        if ($newQuantity < 0) {
            return back()->withErrors(['error' => 'Stock cannot go below zero. Current stock: ' . $product->stock_quantity]);
        }

        $product->update(['stock_quantity' => $newQuantity]);

        return back()->with('success', "Stock for {$product->name} updated to {$newQuantity}.");
    }
}
