<?php

namespace App\Imports\Concerns;

use Maatwebsite\Excel\Validators\Failure;

trait TracksImportStats
{
    public int $totalRows = 0;

    public int $successCount = 0;

    public int $failedCount = 0;

    public array $errors = [];

    protected function trackRow(): void
    {
        $this->totalRows++;
    }

    protected function markSuccess(): void
    {
        $this->successCount++;
    }

    protected function markFailure(string $reason): void
    {
        $this->failedCount++;

        if (count($this->errors) < 200) {
            $this->errors[] = ['row' => $this->totalRows, 'error' => $reason];
        }
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->totalRows++;
            $this->failedCount++;

            if (count($this->errors) < 200) {
                $this->errors[] = [
                    'row' => $failure->row(),
                    'error' => implode('; ', $failure->errors()),
                ];
            }
        }
    }

    public function importStats(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'success' => $this->successCount,
            'failed' => $this->failedCount,
            'errors' => $this->errors,
        ];
    }
}
