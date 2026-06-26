<?php

namespace App\Modules\Messaging\Contracts;

use App\Modules\Messaging\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Model;

interface MessageRecipientPayloadProvider
{
    public function supports(Model $recipient): bool;

    public function destinationForChannel(
        Model $recipient,
        MessageChannel|string $channel,
    ): ?string;
}