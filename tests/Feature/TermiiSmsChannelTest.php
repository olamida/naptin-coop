<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Region;
use App\Models\User;
use App\Notifications\Channels\TermiiSmsChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TermiiSmsChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_routes_sms_to_linked_member_phone(): void
    {
        $region = Region::create(['name' => 'R', 'code' => 'R1', 'state' => 'S', 'enabled' => true]);
        $member = Member::create([
            'region_id' => $region->id,
            'staff_id' => 'S-'.substr(uniqid(), -6),
            'first_name' => 'SMS',
            'last_name' => 'User',
            'phone' => '08012345678',
            'status' => 'active',
        ]);

        $user = User::factory()->create(['member_id' => $member->id]);

        $this->assertSame('08012345678', $user->routeNotificationForTermii());
    }

    public function test_channel_sends_via_termii_api_when_key_configured(): void
    {
        config(['termii.api_key' => 'test-key', 'termii.base_url' => 'https://api.ng.termii.com/api']);

        Http::fake([
            'https://api.ng.termii.com/api/sms/send' => Http::response(['code' => 'ok', 'message_id' => '123'], 200),
        ]);

        $notification = $this->makeNotificationWithTermii();

        $channel = new TermiiSmsChannel;
        $channel->send((new AnonymousNotifiable)->route('sms', '08012345678'), $notification);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.ng.termii.com/api/sms/send'
                && $request['to'] === '08012345678'
                && $request['from'] === 'NAPTIN-COOP'
                && str_contains($request['sms'], 'NAPTIN Coop');
        });
    }

    public function test_channel_is_noop_without_api_key(): void
    {
        config(['termii.api_key' => '']);

        Http::fake();

        $channel = new TermiiSmsChannel;
        $channel->send((new AnonymousNotifiable)->route('sms', '08012345678'), $this->makeNotificationWithTermii());

        Http::assertNothingSent();
    }

    private function makeNotificationWithTermii(): Notification
    {
        return new class extends Notification
        {
            public function via($notifiable): array
            {
                return ['mail', 'database', TermiiSmsChannel::class];
            }

            public function toTermii($notifiable): string
            {
                return 'NAPTIN Coop: Test SMS message.';
            }
        };
    }
}
