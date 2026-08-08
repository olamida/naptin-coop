<?php

namespace App\Http\Controllers\Concerns;

use App\Exports\FinanceReportExport;
use App\Models\Company;
use App\Services\ReportExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Streams a finance report as Excel (Maatwebsite) or PDF (DomPDF). Every PDF
 * carries a QR code embedding a SHA-256 hash of the canonical report dataset
 * plus the plain hash string for manual verification.
 */
trait StreamsReportExports
{
    protected function streamReportExport(
        string $format,
        string $reportKey,
        string $title,
        array $headings,
        array $rows,
        array $pdfData,
        array $moneyColumns = [],
        string $filename = 'finance-report',
    ): mixed {
        if ($format === 'xlsx') {
            return Excel::download(new FinanceReportExport($headings, $rows, $title), $filename.'.xlsx');
        }

        $service = new ReportExportService;
        $hash = $service->hash($reportKey, $pdfData);

        $pdf = Pdf::loadView('finance.exports.pdf-report', array_merge($pdfData, [
            'title' => $title,
            'headings' => $headings,
            'rows' => $rows,
            'moneyColumns' => $moneyColumns,
            'hash' => $hash,
            'qr' => $service->qrPngDataUri($hash),
            'company' => Company::instance(),
            'generated_at' => now(),
        ]))->setPaper('a4', 'landscape');

        return $pdf->stream($filename.'.pdf');
    }
}
