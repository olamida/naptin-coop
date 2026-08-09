<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $company = Company::instance();
        $featuredProducts = Product::where('enabled', true)
            ->where('stock_quantity', '>', 0)
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('company', 'featuredProducts'));
    }

    public function shop(Request $request)
    {
        $query = Product::where('enabled', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        return view('shop', compact('products'));
    }

    public function productSearchJson(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));

        $products = Product::where('enabled', true)
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'unit_price', 'stock_quantity']);

        return response()->json($products->map(fn ($p) => [
            'id' => $p->id,
            'label' => $p->name,
            'sublabel' => '₦'.number_format($p->unit_price, 2).' · '.($p->stock_quantity > 0 ? $p->stock_quantity.' in stock' : 'Out of stock'),
            'url' => null,
        ]));
    }

    public function about()
    {
        $company = Company::instance();

        return view('about', compact('company'));
    }
}
