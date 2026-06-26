<?php

namespace App\Modules\InternalNotifications\Services\Messaging;

use App\Modules\InternalNotifications\Models\TeamMember;
use App\Modules\Messaging\Contracts\MessageRecipientPayloadProvider;
use App\Modules\Messaging\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Model;

class TeamMemberMessageRecipientPayloadProvider implements MessageRecipientPayloadProvider
{
    public function supports(Model $recipient): bool
    {
        return $recipient instanceof TeamMember;
    }

    public function destinationForChannel(
        Model $recipient,
        MessageChannel|string $channel,
    ): ?string {
        if (! $recipient instanceof TeamMember) {
            return null;
        }

        $channel = $channel instanceof MessageChannel
            ? $channel->value
            : strtolower(trim($channel));

        return match ($channel) {
            MessageChannel::Email->value => $recipient->email,
            MessageChannel::Sms->value => $recipient->phone,
            default => null,
        };
    }
}