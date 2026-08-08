<?php

namespace App\Imports;

use App\Imports\Concerns\TracksImportStats;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use TracksImportStats;

    public function __construct(
        public ?string $batchId = null,
    ) {}

    public function model(array $row): ?Product
    {
        $this->trackRow();

        $product = Product::updateOrCreate(
            ['name' => $row['name']],
            [
                'description' => $row['description'] ?? null,
                'unit_price' => round((float) ($row['unit_price'] ?? 0), 2),
                'cost_price' => isset($row['cost_price']) && $row['cost_price'] !== ''
                    ? round((float) $row['cost_price'], 2)
                    : null,
                'stock_quantity' => (int) ($row['stock_quantity'] ?? 0),
                'enabled' => in_array(strtolower($row['enabled'] ?? 'yes'), ['yes', 'true', '1', 'active'], true),
            ]
        );

        $this->markSuccess();

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'enabled' => 'nullable|string|in:yes,no,true,false,1,0,active,inactive',
        ];
    }
}
