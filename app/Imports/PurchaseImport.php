<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PurchaseImport implements ToModel, WithHeadingRow, WithValidation
{
    private string $orderGroup;

    public function __construct()
    {
        $this->orderGroup = 'IMP/' . strtoupper(Str::random(8));
    }

    public function model(array $row): ?PurchaseOrder
    {
        $member = Member::where('staff_id', $row['staff_id'])->first();

        if (! $member) {
            return null;
        }

        $product = Product::where('name', 'like', '%' . $row['product_name'] . '%')->first();

        if (! $product) {
            return null;
        }

        $quantity = (int) ($row['quantity'] ?? 1);
        $unitPrice = round((float) ($row['unit_price'] ?? $product->price), 2);
        $totalAmount = round($quantity * $unitPrice, 2);

        $year = date('Y');
        $count = PurchaseOrder::whereYear('created_at', $year)->count() + 1;
        $orderNumber = 'PUR/' . $year . '/' . str_pad($count, 6, '0', STR_PAD_LEFT);

        return PurchaseOrder::create([
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
        ]);
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|string|exists:members,staff_id',
            'product_name' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
