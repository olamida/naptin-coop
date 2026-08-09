<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Region;
use App\Models\ShareAccount;
use App\Models\ShareTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ShareReceiptTest extends TestCase
{
    use RefreshDatabase;

    private ?User $admin = null;

    private function makeTransaction(string $reference): ShareTransaction
    {
        $region = Region::create([
            'name' => 'Test Region',
            'code' => 'TR'.substr(uniqid(), -6),
            'state' => 'Test State',
            'enabled' => true,
        ]);

        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'STF'.substr(uniqid(), -6),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'monthly_salary' => 100000,
            'status' => 'active',
        ]);

        $account = ShareAccount::create([
            'member_id' => $member->id,
            'total_shares' => 15,
            'total_value' => 1500,
            'share_price' => 100,
            'status' => 'active',
        ]);

        return ShareTransaction::create([
            'share_account_id' => $account->id,
            'reference' => $reference,
            'type' => 'purchase',
            'shares' => 5,
            'amount' => 500,
            'balance_after' => 15,
            'status' => 'completed',
            'transaction_date' => now(),
        ]);
    }

    public function test_share_purchase_receipt_renders_with_reference_and_totals(): void
    {
        $transaction = $this->makeTransaction('SHR/PUR/ABCDEF12');

        $html = View::make('receipts.share-purchase', ['transaction' => $transaction])->render();

        $this->assertStringContainsString('SHR/PUR/ABCDEF12', $html);
        $this->assertStringContainsString('Share Purchase Receipt', $html);
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('5 shares', $html);
        $this->assertStringNotContainsString('reference_number', $html);
    }

    public function test_share_purchase_receipt_route_is_accessible_to_admin(): void
    {
        $token = 'test-session-'.uniqid();
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-receipt-'.uniqid().'@naptin.coop',
            'password' => Hash::make('password'),
        ]);
        $this->admin->forceFill(['active_session_token' => $token])->save();

        $transaction = $this->makeTransaction('SHR/PUR/ABCDEF12');

        $this->withSession(['active_session_token' => $token])
            ->actingAs($this->admin)
            ->get(route('receipts.share-purchase', $transaction))
            ->assertOk()
            ->assertSee('SHR/PUR/ABCDEF12')
            ->assertSee('Share Purchase Receipt');
    }
}
