<?php

namespace App\Messaging\Payloads\Webinars;

use App\Contracts\Messaging\EmailMessagePayload;
use App\Data\WebinarMessageData;
use App\Services\Messaging\EmailMessagingService;

class WebinarConfirmationEmailPayload implements EmailMessagePayload
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

    public function send(EmailMessagingService $emailMessagingService): void
    {
        $emailMessagingService->sendRegistrationConfirmation($this->data);
    }
}