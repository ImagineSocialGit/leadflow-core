<?php

namespace App\Services\Messaging;

use App\Enums\MessageChannel;
use App\Models\TeamMember;

class InternalNotificationChannelResolver
{
    public function __construct(
        private readonly InternalNotificationGate $gate,
    ) {}

    /**
     * @param array<int, MessageChannel|string> $allowedChannels
     */
    public function resolve(
        TeamMember $teamMember,
        ?string $notificationType = null,
        array $allowedChannels = [MessageChannel::Email, MessageChannel::Sms],
    ): ?MessageChannel {
        foreach ($allowedChannels as $channel) {
            $channel = $this->normalizeChannel($channel);

            if (
                $channel instanceof MessageChannel
                && $this->gate->allows($teamMember, $channel, $notificationType)
            ) {
                return $channel;
            }
        }

        return null;
    }

    private function normalizeChannel(MessageChannel|string $channel): ?MessageChannel
    {
        if ($channel instanceof MessageChannel) {
            return $channel;
        }

        return MessageChannel::tryFrom(strtolower(trim($channel)));
    }
}