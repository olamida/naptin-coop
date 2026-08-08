<?php

namespace App\Imports;

use App\Imports\Concerns\TracksImportStats;
use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PurchaseImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use TracksImportStats;

    private string $orderGroup;

    public function __construct(
        public ?string $batchId = null,
    ) {
        $this->orderGroup = 'IMP/'.strtoupper(substr($batchId ?? Str::uuid(), 0, 8));
    }

    public function model(array $row): ?PurchaseOrder
    {
        $this->trackRow();

        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (! $member) {
            $this->markFailure('No member found for staff_id '.$row['staff_id']);

            return null;
        }

        $product = Product::where('name', 'like', '%'.$row['product_name'].'%')->first();

        if (! $product) {
            $this->markFailure('No product found matching "'.$row['product_name'].'"');

            return null;
        }

        $quantity = (int) ($row['quantity'] ?? 1);
        $unitPrice = round((float) ($row['unit_price'] ?? $product->price), 2);
        $totalAmount = round($quantity * $unitPrice, 2);

        $year = date('Y');
        $count = PurchaseOrder::whereYear('created_at', $year)->count() + 1;
        $orderNumber = 'PUR/'.$year.'/'.str_pad($count, 6, '0', STR_PAD_LEFT);

        $order = PurchaseOrder::create([
            'order_number' => $orderNumber,
            'order_group' => $this->orderGroup,
            'member_id' => $member->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'payment_type' => 'salary_deduction',
            'monthly_repayment' => 0,
            'amount_paid' => 0,
            'status' => 'pending',
            'import_batch_id' => $this->batchId,
            'external_reference' => $row['external_reference'] ?? null,
        ]);

        $this->markSuccess();

        return $order;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|exists:members,staff_id',
            'product_name' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'external_reference' => 'nullable|string|max:100',
        ];
    }
}
