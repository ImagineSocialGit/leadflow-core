<?php

namespace Tests\Feature\Webhooks;

use App\Actions\Webinars\RecordZoomAttendanceAction;
use App\Services\Zoom\ZoomWebinarService;
use Mockery\MockInterface;
use Tests\TestCase;

class ZoomWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'webinars.provider' => 'zoom',
            'services.zoom.webhook_secret' => 'test_zoom_webhook_secret',
        ]);
    }

    public function test_it_handles_zoom_url_validation(): void
    {
        $plainToken = 'plain-token-from-zoom';

        $response = $this->postJson(route('webhooks.zoom'), [
            'event' => 'endpoint.url_validation',
            'payload' => [
                'plainToken' => $plainToken,
            ],
        ]);

        $response->assertOk();

        $response->assertExactJson([
            'plainToken' => $plainToken,
            'encryptedToken' => hash_hmac(
                'sha256',
                $plainToken,
                config('services.zoom.webhook_secret')
            ),
        ]);
    }

    public function test_it_rejects_invalid_signatures(): void
    {
        $response = $this
            ->withHeaders([
                'x-zm-request-timestamp' => (string) time(),
                'x-zm-signature' => 'v0=invalid-signature',
            ])
            ->postJson(route('webhooks.zoom'), [
                'event' => 'webinar.ended',
                'payload' => [
                    'object' => [
                        'id' => '123456789',
                    ],
                ],
            ]);

        $response->assertUnauthorized();
    }

    public function test_it_ignores_irrelevant_signed_events(): void
    {
        $payload = [
            'event' => 'webinar.started',
            'payload' => [
                'object' => [
                    'id' => '123456789',
                ],
            ],
        ];

        $response = $this->signedZoomPost($payload);

        $response->assertNoContent();
    }

    public function test_it_ignores_supported_events_without_a_webinar_id(): void
    {
        $payload = [
            'event' => 'webinar.ended',
            'payload' => [
                'object' => [],
            ],
        ];

        $response = $this->signedZoomPost($payload);

        $response->assertNoContent();
    }

    public function test_it_processes_webinar_ended_events(): void
    {
        $webinarId = '123456789';

        $participants = collect([
            [
                'id' => 'participant-1',
                'user_email' => 'person@example.com',
                'duration' => 3600,
            ],
        ]);

        $this->mock(ZoomWebinarService::class, function (MockInterface $mock) use ($webinarId, $participants) {
            $mock->shouldReceive('listPastWebinarParticipants')
                ->once()
                ->with($webinarId)
                ->andReturn($participants);
        });

        $this->mock(RecordZoomAttendanceAction::class, function (MockInterface $mock) use ($webinarId, $participants) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($webinarId, $participants);
        });

        $response = $this->signedZoomPost([
            'event' => 'webinar.ended',
            'payload' => [
                'object' => [
                    'id' => $webinarId,
                ],
            ],
        ]);

        $response->assertNoContent();
    }

    public function test_it_processes_webinar_completed_events(): void
    {
        $webinarId = '987654321';

        $participants = collect([
            [
                'id' => 'participant-1',
                'user_email' => 'person@example.com',
                'duration' => 3600,
            ],
        ]);

        $this->mock(ZoomWebinarService::class, function (MockInterface $mock) use ($webinarId, $participants) {
            $mock->shouldReceive('listPastWebinarParticipants')
                ->once()
                ->with($webinarId)
                ->andReturn($participants);
        });

        $this->mock(RecordZoomAttendanceAction::class, function (MockInterface $mock) use ($webinarId, $participants) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($webinarId, $participants);
        });

        $response = $this->signedZoomPost([
            'event' => 'webinar.completed',
            'payload' => [
                'object' => [
                    'id' => $webinarId,
                ],
            ],
        ]);

        $response->assertNoContent();
    }

    public function test_it_returns_not_found_for_unsupported_webinar_providers(): void
    {
        config([
            'webinars.provider' => 'unsupported',
        ]);

        $response = $this->postJson(route('webhooks.zoom'), [
            'event' => 'endpoint.url_validation',
            'payload' => [
                'plainToken' => 'plain-token-from-zoom',
            ],
        ]);

        $response->assertNotFound();
    }

    private function signedZoomPost(array $payload)
    {
        $timestamp = (string) time();

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);

        $signature = 'v0='.hash_hmac(
            'sha256',
            'v0:'.$timestamp.':'.$body,
            config('services.zoom.webhook_secret')
        );

        return $this->call(
            method: 'POST',
            uri: route('webhooks.zoom'),
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_ZM_REQUEST_TIMESTAMP' => $timestamp,
                'HTTP_X_ZM_SIGNATURE' => $signature,
            ],
            content: $body
        );
    }
}
