<?php

namespace App\Messaging\Payloads\Webinars;

use App\Contracts\Messaging\SmsMessagePayload;
use App\Data\WebinarMessageData;
use App\Services\Messaging\SmsMessagingService;

class WebinarConfirmationSmsPayload implements SmsMessagePayload
{
    public function __construct(
        public WebinarMessageData $data,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            data: WebinarMessageData::fromArray($payload),
        );
    }

    public function send(SmsMessagingService $smsMessagingService): void
    {
        $smsMessagingService->sendRegistrationConfirmation($this->data);
    }
}