<?php

namespace App\Modules\Messaging\Services;

use App\Modules\Messaging\Contracts\MessageRecipientPayloadProvider;
use App\Modules\Messaging\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Model;

class MessageRecipientPayloadProviderRegistry
{
    /**
     * @param iterable<int, MessageRecipientPayloadProvider> $providers
     */
    public function __construct(
        private readonly iterable $providers,
    ) {}

    public function destinationForChannel(
        Model $recipient,
        MessageChannel|string $channel,
    ): ?string {
        foreach ($this->providers as $provider) {
            if (! $provider->supports($recipient)) {
                continue;
            }

            $destination = $provider->destinationForChannel($recipient, $channel);

            if (is_string($destination) && trim($destination) !== '') {
                return trim($destination);
            }
        }

        return null;
    }
}