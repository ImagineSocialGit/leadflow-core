<?php

namespace App\Data\Webinars;

class ProviderWebhookEvent
{
    public function __construct(
        public readonly string $provider,
        public readonly string $event,
        public readonly ?string $externalWebinarId = null,
        public readonly ?string $externalWebinarUuid = null,
        public readonly array $payload = [],
    ) {}

    public function isWebinarEnded(): bool
    {
        return in_array($this->event, [
            'webinar.ended',
            'webinar.completed',
        ], true);
    }
}