<?php

namespace App\Http\Controllers;

use App\Imports\PurchaseImport;
use App\Models\ImportLog;
use App\Models\Member;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchasesController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['member', 'product'])
            ->selectRaw('order_group, member_id, payment_type, status, MIN(created_at) as created_at, SUM(total_amount) as total_amount, COUNT(*) as item_count')
            ->groupBy('order_group', 'member_id', 'payment_type', 'status');

        if ($search = $request->input('search')) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_id', 'like', "%{$search}%");
            })->orWhere('order_group', 'like', "%{$search}%");
        }

        if ($memberId = $request->input('member_id')) {
            $query->where('member_id', $memberId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $perPage = $request->input('per_page');
        $orders = $query->latest('created_at')->paginate($perPage === 'all' ? 1000 : 15)->withQueryString();

        $members = Member::where('status', 'active')->orderBy('first_name')->get();

        return view('purchases.index', compact('orders', 'members'));
    }

    public function create(Request $request): View
    {
        $memberId = $request->input('member_id');
        $member = $memberId ? Member::find($memberId) : null;

        return view('purchases.create', ['member' => $member, 'memberId' => $memberId]);
    }

    public function import(): View
    {
        return view('purchases.import');
    }

    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $batchId = (string) Str::uuid();
        $import = new PurchaseImport($batchId);
        $fileName = $request->file('import_file')->getClientOriginalName();

        try {
            Excel::import($import, $request->file('import_file'));

            ImportLog::record($batchId, 'purchase_orders', $fileName, $import->importStats());

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase orders imported successfully. Batch: '.substr($batchId, 0, 8).'…');
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'purchase_orders', $fileName, $import->importStats(), 'failed', $e->getMessage());

            return back()->withErrors(['import_file' => 'Import failed: '.$e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="purchase_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['staff_id', 'product_name', 'quantity', 'unit_price', 'payment_date', 'notes']);
            fputcsv($file, ['STF001', 'Product A', '2', '5000', '2026-01-15', 'January salary deduction - purchase']);
            fputcsv($file, ['STF002', 'Product B', '1', '15000', '2026-01-15', 'January salary deduction - purchase']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
