<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->string('subtype')->nullable()->after('type'); // current_asset, reserve, operating_income, ...
            $table->boolean('is_control_account')->default(false)->after('normal_side');
            $table->string('control_module')->nullable()->after('is_control_account'); // savings, loans, shares, inventory, payroll, dividends
            $table->boolean('allow_manual_entry')->default(true)->after('is_active');
        });

        // Backfill the new columns so the chart carries the CBN control flags from day one.
        $flags = [
            // control accounts (must reconcile with their sub-ledger)
            '1101' => ['is_control_account' => true, 'control_module' => 'loans'],
            '1201' => ['is_control_account' => true, 'control_module' => 'purchases'],
            '1301' => ['is_control_account' => true, 'control_module' => 'inventory'],
            '1501' => ['is_control_account' => true, 'control_module' => 'payroll'],
            '2001' => ['is_control_account' => true, 'control_module' => 'savings'],
            '2101' => ['is_control_account' => true, 'control_module' => 'shares'],
            '2201' => ['is_control_account' => true, 'control_module' => 'dividends'],
            // manual-entry-restricted accounts (only auto-posted / reconciliation posts)
            '1205' => ['allow_manual_entry' => false],
            '4001' => ['allow_manual_entry' => false],
            '4002' => ['allow_manual_entry' => false],
            '4004' => ['allow_manual_entry' => false],
            '4005' => ['allow_manual_entry' => false],
            '5004' => ['allow_manual_entry' => false],
        ];

        foreach ($flags as $code => $values) {
            DB::table('chart_of_accounts')->where('code', $code)->update($values);
        }
    }

    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn(['subtype', 'is_control_account', 'control_module', 'allow_manual_entry']);
        });
    }
};
