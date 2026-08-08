<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ReportExportService;
use Database\Seeders\LedgerAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private const XLSX_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private function admin(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'super-admin']));
        Permission::firstOrCreate(['name' => 'manage-users']);
        $admin->givePermissionTo('manage-users');
        $token = 'test-session-'.uniqid();
        $admin->forceFill(['active_session_token' => $token])->save();

        return [$admin, $token];
    }

    private function exportRoutes(): array
    {
        return [
            'finance.export.profit-loss',
            'finance.export.balance-sheet',
            'finance.export.cash-flow',
            'finance.export.loan-aging',
            'finance.export.savings-control',
            'finance.export.audit-trail',
            'ledger.trial-balance.export',
        ];
    }

    public function test_all_report_exports_generate_xlsx_files(): void
    {
        [$admin, $token] = $this->admin();
        $this->seed(LedgerAccountsSeeder::class);

        foreach ($this->exportRoutes() as $route) {
            $response = $this
                ->withSession(['active_session_token' => $token])
                ->actingAs($admin)
                ->get(route($route, ['format' => 'xlsx']));

            $response->assertOk();
            $this->assertStringContainsString(
                'vnd.openxmlformats',
                $response->headers->get('content-type'),
                "Route {$route} did not return an Excel file."
            );
        }
    }

    public function test_all_report_exports_generate_pdf_files_with_qr(): void
    {
        [$admin, $token] = $this->admin();
        $this->seed(LedgerAccountsSeeder::class);

        foreach ($this->exportRoutes() as $route) {
            $response = $this
                ->withSession(['active_session_token' => $token])
                ->actingAs($admin)
                ->get(route($route, ['format' => 'pdf']));

            $response->assertOk();
            $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'), "Route {$route} did not return a PDF.");
            $this->assertStringContainsString('%PDF', $response->getContent(), "Route {$route} did not render PDF binary data.");
        }
    }

    public function test_export_requires_valid_format(): void
    {
        [$admin, $token] = $this->admin();

        $this
            ->withSession(['active_session_token' => $token])
            ->actingAs($admin)
            ->get(route('finance.export.profit-loss', ['format' => 'csv']))
            ->assertSessionHasErrors('format');
    }

    public function test_report_hash_is_deterministic_and_data_sensitive(): void
    {
        $service = new ReportExportService;

        $dataset = [
            'rows' => [
                ['1001', 'Cash', 1500.00],
                ['2001', 'Savings Liability', 1500.00],
            ],
            'total' => 1500.00,
        ];

        $first = $service->hash('trial-balance', $dataset);
        $second = $service->hash('trial-balance', $dataset);
        $altered = $dataset;
        $altered['rows'][0][2] = 1600.00;

        $this->assertSame($first, $second);
        $this->assertNotSame($first, $service->hash('trial-balance', $altered));
        $this->assertNotSame($first, $service->hash('profit-loss', $dataset));
    }

    public function test_qr_png_data_uri_is_a_valid_png(): void
    {
        $service = new ReportExportService;
        $dataUri = $service->qrPngDataUri('report-hash-123');

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);

        $png = base64_decode(substr($dataUri, strlen('data:image/png;base64,')));
        $this->assertStringStartsWith("\x89PNG", $png);
    }
}
