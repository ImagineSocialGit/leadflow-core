<?php

namespace Tests\Feature\Messaging;

use App\Data\WebinarMessageData;
use App\Services\Messaging\SmsSendGuard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SmsSendGuardTest extends TestCase
{
    public function test_it_allows_sms_when_no_limits_are_exceeded(): void
    {
        $guard = app(SmsSendGuard::class);

        $this->assertTrue(
            $guard->allows($this->messageData(), '+15555555555', 'Test message', 'test')
        );
    }

    public function test_it_blocks_duplicate_sms_within_duplicate_window(): void
    {
        config(['sms.cooldowns.duplicate_window_minutes' => 15]);

        $guard = app(SmsSendGuard::class);
        $data = $this->messageData();
        $to = '+15555555555';
        $message = 'Test message';

        $guard->record($data, $to, $message, 'test');

        $this->assertFalse(
            $guard->allows($data, $to, $message, 'test')
        );
    }

    public function test_it_blocks_sms_when_phone_daily_limit_is_exceeded(): void
    {
        config(['sms.rate_limits.per_phone_per_day' => 1]);

        $guard = app(SmsSendGuard::class);
        $data = $this->messageData();
        $to = '+15555555555';

        $guard->record($data, $to, 'First message', 'test');

        $this->assertFalse(
            $guard->allows($data, $to, 'Second message', 'test')
        );
    }

    public function test_it_blocks_sms_when_ip_hourly_limit_is_exceeded(): void
    {
        config(['sms.rate_limits.per_ip_per_hour' => 1]);

        $guard = app(SmsSendGuard::class);
        $data = $this->messageData([
            'requestIp' => '203.0.113.10',
        ]);

        $guard->record($data, '+15555555555', 'First message', 'test');

        $this->assertFalse(
            $guard->allows($data, '+15555555556', 'Second message', 'test')
        );
    }

    protected function tearDown(): void
    {
        Cache::flush();
        RateLimiter::clear('sms:phone:daily:+15555555555:'.now()->toDateString());
        RateLimiter::clear('sms:phone:daily:+15555555556:'.now()->toDateString());
        RateLimiter::clear('sms:ip:hourly:203.0.113.10');

        parent::tearDown();
    }

    private function messageData(array $overrides = []): WebinarMessageData
    {
        return new WebinarMessageData(
            registrationId: $overrides['registrationId'] ?? 1,
            leadId: $overrides['leadId'] ?? 1,
            leadFirstName: $overrides['leadFirstName'] ?? 'Test',
            leadEmail: $overrides['leadEmail'] ?? 'test@example.com',
            leadPhone: $overrides['leadPhone'] ?? '+15555555555',
            webinarId: $overrides['webinarId'] ?? 1,
            webinarSlug: $overrides['webinarSlug'] ?? 'test-webinar',
            webinarTitle: $overrides['webinarTitle'] ?? 'Test Webinar',
            webinarStartsAt: $overrides['webinarStartsAt'] ?? now()->addDay(),
            webinarTimezone: $overrides['webinarTimezone'] ?? 'America/Chicago',
            webinarJoinUrl: $overrides['webinarJoinUrl'] ?? 'https://example.com/join',
            webinarPlatform: $overrides['webinarPlatform'] ?? 'zoom',
            requestIp: $overrides['requestIp'] ?? null,
        );
    }
}