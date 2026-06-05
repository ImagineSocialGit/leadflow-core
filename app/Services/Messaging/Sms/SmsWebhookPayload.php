<?php

namespace App\Services\Messaging\Sms;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SmsWebhookPayload
{
    public function __construct(
        public readonly string $provider,
        public readonly ?string $providerMessageId,
        public readonly ?string $from,
        public readonly ?string $to,
        public readonly ?string $body,
        public readonly ?Carbon $receivedAt,
        public readonly string $source,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly array $raw = [],
    ) {}

    public static function fromRequest(
        string $provider,
        Request $request,
        ?string $providerMessageId,
        ?string $from,
        ?string $to,
        ?string $body,
        ?Carbon $receivedAt = null,
    ): self {
        return new self(
            provider: $provider,
            providerMessageId: $providerMessageId,
            from: $from,
            to: $to,
            body: $body,
            receivedAt: $receivedAt,
            source: $provider.'_inbound_sms',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            raw: $request->all(),
        );
    }

    public function normalizedBody(): ?string
    {
        if (! is_string($this->body)) {
            return null;
        }

        $body = strtoupper(trim($this->body));

        return $body === '' ? null : $body;
    }

    public function normalizedFrom(): ?string
    {
        if (! is_string($this->from)) {
            return null;
        }

        $from = trim($this->from);

        return $from === '' ? null : $from;
    }
}