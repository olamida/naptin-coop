<?php

namespace App\Http\Controllers;

use App\Exports\OnboardingTemplateExport;
use App\Imports\OnboardingImport;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class OnboardingController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('admin.onboarding.index');
    }

    public function importStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $batchId = (string) Str::uuid();
        $fileName = $request->file('import_file')->getClientOriginalName();
        $onboarding = new OnboardingImport($batchId);

        try {
            DB::transaction(function () use ($onboarding, $request) {
                Excel::import($onboarding, $request->file('import_file'));
            });

            $stats = $onboarding->aggregateStats();

            ImportLog::record($batchId, 'onboarding', $fileName, $stats);

            return redirect()->route('admin.onboarding')
                ->with('success', 'Onboarding completed. Batch: ' . substr($batchId, 0, 8)
                    . '… (' . $stats['success'] . ' rows imported, ' . $stats['failed'] . ' failed).');
        } catch (\Exception $e) {
            ImportLog::record($batchId, 'onboarding', $fileName, $onboarding->aggregateStats(), 'failed', $e->getMessage());

            return back()->withErrors(['import_file' => 'Onboarding failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new OnboardingTemplateExport, 'onboarding_template.xlsx');
    }
}
