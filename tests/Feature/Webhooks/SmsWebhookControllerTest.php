<?php

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_telnyx_invalid_signature_is_rejected(): void
    {
        config(['services.telnyx.webhook_public_key' => str_repeat('a', 64)]);

        $this
            ->withHeaders([
                'Telnyx-Signature-Ed25519' => str_repeat('b', 128),
                'Telnyx-Timestamp' => (string) now()->timestamp,
            ])
            ->postJson(route('webhooks.sms', ['provider' => 'telnyx']), $this->telnyxPayload(body: 'STOP'))
            ->assertForbidden();
    }

    private function telnyxPayload(string $body, string $from = '+15555550123'): array
    {
        return [
            'data' => [
                'event_type' => 'message.received',
                'id' => 'event-id',
                'payload' => [
                    'id' => 'message-id',
                    'from' => [
                        'phone_number' => $from,
                    ],
                    'to' => [
                        [
                            'phone_number' => '+15555550000',
                        ],
                    ],
                    'text' => $body,
                    'received_at' => now()->toIso8601String(),
                ],
            ],
        ];
    }
}