<?php

namespace App\Contracts\Messaging;

use App\Services\Messaging\SmsMessagingService;

interface SmsMessagePayload
{
    public static function fromArray(array $payload): self;

    public function send(SmsMessagingService $smsMessagingService): void;
}