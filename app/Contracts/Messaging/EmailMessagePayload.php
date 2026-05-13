<?php

namespace App\Contracts\Messaging;

use App\Services\Messaging\EmailMessagingService;

interface EmailMessagePayload
{
    public static function fromArray(array $payload): self;

    public function send(EmailMessagingService $emailMessagingService): void;
}