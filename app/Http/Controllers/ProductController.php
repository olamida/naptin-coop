<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Models\ImportLog;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
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

        return view('products.index', ['products' => $products, 'isAdmin' => $isAdmin, 'memberId' => $memberId, 'orderMember' => $orderMember]);
    }

    public function create(): \Illuminate\View\View
    {
        return view('products.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
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

    public function edit(Product $product): \Illuminate\View\View
    {
        return view('products.edit', ['product' => $product]);
    }

    public function update(Request $request, Product $product): \Illuminate\Http\RedirectResponse
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
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('product-images', 'public');
        }

        unset($validated['image'], $validated['remove_image']);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function orders(Request $request): \Illuminate\View\View
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

    public function showOrderGroup(string $orderGroup): \Illuminate\View\View
    {
        $orders = PurchaseOrder::with(['member', 'product', 'approvedBy'])
            ->where('order_group', $orderGroup)
            ->get();

        if ($orders->isEmpty()) {
            abort(404);
        }

        return view('products.show-order', ['orders' => $orders, 'orderGroup' => $orderGroup]);
    }

    public function createOrder(): \Illuminate\View\View
    {
        $members = Member::where('status', 'active')->orderBy('first_name')->get();
        $products = Product::where('enabled', true)->orderBy('name')->get();

        return view('products.create-order', compact('members', 'products'));
    }

    public function storeOrder(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'payment_type' => 'required|in:cash,hire_purchase',
            'monthly_repayment' => 'required_if:payment_type,hire_purchase|nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $quantity = $validated['quantity'];
        $unitPrice = $product->unit_price;
        $totalAmount = round($quantity * $unitPrice, 2);

        if ($quantity > $product->stock_quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $product->stock_quantity])->withInput();
        }

        $cartService = new \App\Services\CartService();
        $orderNumber = $cartService->generateOrderNumber();

        $status = $validated['payment_type'] === 'cash' ? 'approved' : 'pending';

        $order = null;
        DB::transaction(function () use ($validated, $product, $quantity, $unitPrice, $totalAmount, $orderNumber, $status, &$order) {
            $product->lockForUpdate();
            if ($quantity > $product->stock_quantity) {
                throw new \Exception('Insufficient stock. Available: ' . $product->stock_quantity);
            }

            $order = PurchaseOrder::create([
                'order_number' => $orderNumber,
                'member_id' => $validated['member_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'payment_type' => $validated['payment_type'],
                'monthly_repayment' => $validated['monthly_repayment'] ?? 0,
                'status' => $status,
            ]);

            $product->decrement('stock_quantity', $quantity);

            $ledger = app(\App\Services\LedgerService::class);
            if ($validated['payment_type'] === 'cash') {
                $ledger->postCashSale($order->id, $totalAmount);
            } else {
                $ledger->postHirePurchaseSale($order->id, $totalAmount);
            }
        });

        return redirect()->route('products.orders')
            ->with('success', "Purchase order {$orderNumber} created successfully.");
    }

    public function approveOrder(PurchaseOrder $order): \Illuminate\Http\RedirectResponse
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

    public function collectOrder(PurchaseOrder $order): \Illuminate\Http\RedirectResponse
    {
        if (!in_array($order->status, ['approved', 'active'])) {
            return back()->withErrors(['error' => 'Order must be approved before collection.']);
        }

        $order->update([
            'status' => $order->payment_type === 'hire_purchase' ? 'active' : 'completed',
            'collected_at' => now(),
        ]);

        return back()->with('success', 'Product collected successfully.');
    }

    public function import(): \Illuminate\View\View
    {
        return view('products.import');
    }

    public function importStore(Request $request): \Illuminate\Http\RedirectResponse
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
                ->with('success', 'Products imported successfully. Batch: ' . substr($batchId, 0, 8) . '…');
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'products', $fileName, $import->importStats(), 'failed', $e->getMessage());

            return back()->withErrors(['import_file' => 'Import failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
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
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
