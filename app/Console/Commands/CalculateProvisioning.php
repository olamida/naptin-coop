<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\PeriodClose;
use App\Services\ProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateProvisioning extends Command
{
    protected $signature = 'app:calculate-provisioning';

    protected $description = 'Run the CBN/IFRS 9 loan-loss provisioning for the current month and post the net ledger movement';

    public function handle(): int
    {
        $period = now()->format('Y-m');

        if (PeriodClose::isClosed($period)) {
            $this->warn("Financial period {$period} is closed — provisioning skipped (posting would be rejected).");

            return self::SUCCESS;
        }

        try {
            $result = ProvisioningService::calculate();
        } catch (\Throwable $e) {
            ActivityLog::log('provisioning_failed', "Scheduled provisioning failed for {$period}: {$e->getMessage()}");
            Log::error('Scheduled provisioning failed', ['period' => $period, 'error' => $e->getMessage()]);

            $this->error('Provisioning failed: '.$e->getMessage());

            return self::FAILURE;
        }

        ActivityLog::log(
            'provisioning_completed',
            "Scheduled provisioning for {$period}: required ₦".number_format($result['total_provision'], 2)." across {$result['total_outstanding']} outstanding (coverage {$result['coverage_ratio']}%)"
        );

        $this->info(
            "Provisioning for {$period}: ₦".number_format($result['total_provision'], 2).' provision against ₦'.number_format($result['total_outstanding'], 2)." outstanding (coverage {$result['coverage_ratio']}%)"
        );

        return self::SUCCESS;
    }
}
