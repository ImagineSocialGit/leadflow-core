<?php

namespace App\Services\Messaging;

use App\Contracts\Services\Messaging\SmsWebhookHandler;
use App\Services\Messaging\Providers\TelnyxSmsWebhookHandler;
use App\Services\Messaging\Providers\TwilioSmsWebhookHandler;
use InvalidArgumentException;

class SmsWebhookHandlerResolver
{
    /**
     * @param array<string, SmsWebhookHandler> $handlers
     */
    public function __construct(
        private readonly array $handlers,
    ) {}

    public static function default(): self
    {
        return new self([
            'twilio' => app(TwilioSmsWebhookHandler::class),
            'telnyx' => app(TelnyxSmsWebhookHandler::class),
        ]);
    }

    public function resolve(string $provider): SmsWebhookHandler
    {
        $provider = strtolower(trim($provider));

        if (! isset($this->handlers[$provider])) {
            throw new InvalidArgumentException("Unsupported SMS webhook provider [{$provider}].");
        }

        return $this->handlers[$provider];
    }
}