<?php

namespace App\Services\Messaging;

use App\Enums\MessageChannel;
use App\Enums\MessagePurpose;
use App\Models\ConsentRevocation;
use App\Models\Contact;
use App\Models\MessageConsent;

class MessageEligibilityGate
{
    public function __construct(
        private readonly MessageSuppressionService $messageSuppressionService,
    ) {}

    public function canSend(Contact $contact, MessageChannel|string $channel, MessagePurpose|string $purpose): bool
    {
        $channel = $this->normalizeChannel($channel);
        $purpose = $this->normalizePurpose($purpose);

        $destination = $this->destinationFor($contact, $channel);

        if (! $destination) {
            return false;
        }

        if (! $this->hasActiveConsent($contact, $channel, $purpose)) {
            return false;
        }

        return ! $this->messageSuppressionService->isSuppressed($channel, $destination);
    }

    private function hasActiveConsent(Contact $contact, string $channel, string $purpose): bool
    {
        $latestConsent = MessageConsent::query()
            ->where('contact_id', $contact->getKey())
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->latest('consented_at')
            ->first();

        if (! $latestConsent) {
            return false;
        }

        return ! ConsentRevocation::query()
            ->where('contact_id', $contact->getKey())
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->where('revoked_at', '>=', $latestConsent->consented_at)
            ->exists();
    }

    private function destinationFor(Contact $contact, string $channel): ?string
    {
        return match ($channel) {
            MessageChannel::Sms->value => $contact->phone ?? null,
            MessageChannel::Email->value => $contact->email ?? null,
            default => null,
        };
    }

    private function normalizeChannel(MessageChannel|string $channel): string
    {
        return $channel instanceof MessageChannel
            ? $channel->value
            : strtolower(trim($channel));
    }

    private function normalizePurpose(MessagePurpose|string $purpose): string
    {
        return $purpose instanceof MessagePurpose
            ? $purpose->value
            : strtolower(trim($purpose));
    }
}