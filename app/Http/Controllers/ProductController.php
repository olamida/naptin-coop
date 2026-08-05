<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Models\ImportLog;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\CartService;
use App\Services\LedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $isAdmin = auth()->user()->hasAnyRole(['super-admin', 'admin']);
        $query = Product::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        $memberId = $request->input('member_id');
        $orderMember = $memberId ? Member::find($memberId) : null;

        $cartCount = (new CartService('admin', auth()->id()))->counts()['cart_count'];

        return view('products.index', ['products' => $products, 'isAdmin' => $isAdmin, 'memberId' => $memberId, 'orderMember' => $orderMember, 'cartCount' => $cartCount]);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product-images', 'public');
        }

        Product::create(array_merge(
            collect($validated)->except(['image'])->toArray(),
            ['image_path' => $imagePath]
        ));

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'enabled' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'boolean',
        ]);

        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('product-images', 'public');
        }

        unset($validated['image'], $validated['remove_image']);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function orders(Request $request): View
    {
        $query = PurchaseOrder::with(['member', 'product'])
            ->selectRaw('order_group, member_id, payment_type, status, MIN(created_at) as created_at, SUM(total_amount) as total_amount, COUNT(*) as item_count')
            ->groupBy('order_group', 'member_id', 'payment_type', 'status');

        if ($search = $request->input('search')) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            })->orWhere('order_group', 'like', "%{$search}%")
                ->orWhere('order_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentType = $request->input('payment_type')) {
            $query->where('payment_type', $paymentType);
        }

        $perPage = $request->input('per_page');
        $orders = $query->latest('created_at')->paginate($perPage === 'all' ? 1000 : 15)->withQueryString();

        return view('products.orders', ['orders' => $orders]);
    }

    public function showOrderGroup(string $orderGroup): View
    {
        $orders = PurchaseOrder::with(['member', 'product', 'approvedBy'])
            ->where('order_group', $orderGroup)
            ->get();

        if ($orders->isEmpty()) {
            abort(404);
        }

        return view('products.show-order', ['orders' => $orders, 'orderGroup' => $orderGroup]);
    }

    public function createOrder(): View
    {
        $members = Member::where('status', 'active')->orderBy('first_name')->get();
        $products = Product::where('enabled', true)->orderBy('name')->get();

        return view('products.create-order', compact('members', 'products'));
    }

    public function storeOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'payment_type' => 'required|in:cash,hire_purchase',
            'monthly_repayment' => 'required_if:payment_type,hire_purchase|nullable|numeric|min:0',
            'is_society_expense' => 'boolean',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = $validated['quantity'];
        $unitPrice = $product->unit_price;
        $totalAmount = round($quantity * $unitPrice, 2);

        if ($quantity > $product->stock_quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: '.$product->stock_quantity])->withInput();
        }

        $cartService = new CartService;
        $orderNumber = $cartService->generateOrderNumber();

        $status = $validated['payment_type'] === 'cash' ? 'approved' : 'pending';
        $isSocietyExpense = $request->boolean('is_society_expense');

        $order = null;
        DB::transaction(function () use ($validated, $product, $quantity, $unitPrice, $totalAmount, $orderNumber, $status, $isSocietyExpense, &$order) {
            $product->lockForUpdate();
            if ($quantity > $product->stock_quantity) {
                throw new \Exception('Insufficient stock. Available: '.$product->stock_quantity);
            }

            $order = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'member_id' => $validated['member_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'payment_type' => $validated['payment_type'],
                'is_society_expense' => $isSocietyExpense,
                'monthly_repayment' => $validated['monthly_repayment'] ?? 0,
                'status' => $status,
            ]);

            $product->decrement('stock_quantity', $quantity);

            $ledger = app(LedgerService::class);
            if ($isSocietyExpense) {
                $ledger->postSocietyExpense($order->id, $totalAmount);
            } elseif ($validated['payment_type'] === 'cash') {
                $ledger->postCashSale($order->id, $totalAmount);
            } else {
                $ledger->postHirePurchaseSale($order->id, $totalAmount);
            }
        });

        return redirect()->route('products.orders')
            ->with('success', "Purchase order {$orderNumber} created successfully.");
    }

    public function approveOrder(PurchaseOrder $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending orders can be approved.']);
        }

        $order->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Purchase order approved successfully.');
    }

    public function collectOrder(PurchaseOrder $order): RedirectResponse
    {
        if (! in_array($order->status, ['approved', 'active'])) {
            return back()->withErrors(['error' => 'Order must be approved before collection.']);
        }

        $order->update([
            'status' => $order->payment_type === 'hire_purchase' ? 'active' : 'completed',
            'collected_at' => now(),
        ]);

        return back()->with('success', 'Product collected successfully.');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $newQuantity = $product->stock_quantity + $validated['adjustment'];
        if ($newQuantity < 0) {
            return back()->withErrors(['error' => 'Stock cannot go below zero. Current stock: '.$product->stock_quantity]);
        }

        $product->update(['stock_quantity' => $newQuantity]);

        return back()->with('success', "Stock for {$product->name} updated to {$newQuantity}.");
    }

    public function import(): View
    {
        return view('products.import');
    }

    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $batchId = (string) Str::uuid();
        $import = new ProductImport($batchId);
        $fileName = $request->file('import_file')->getClientOriginalName();

        try {
            Excel::import($import, $request->file('import_file'));

            ImportLog::record($batchId, 'products', $fileName, $import->importStats());

            return redirect()->route('products.index')
                ->with('success', 'Products imported successfully. Batch: '.substr($batchId, 0, 8).'…');
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'products', $fileName, $import->importStats(), 'failed', $e->getMessage());

            return back()->withErrors(['import_file' => 'Import failed: '.$e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate(): StreamedResponse
    {
        $filename = 'products_template.csv';
        $headers = ['name', 'description', 'unit_price', 'stock_quantity', 'enabled'];
        $sample = ['Widget A', 'A useful widget', '1500.00', '100', 'yes'];

        return response()->stream(function () use ($headers, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, $sample);
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
