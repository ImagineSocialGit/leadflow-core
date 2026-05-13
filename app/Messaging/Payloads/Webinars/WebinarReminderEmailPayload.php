<?php

namespace App\Messaging\Payloads\Webinars;

use App\Contracts\Messaging\EmailMessagePayload;
use App\Data\WebinarMessageData;
use App\Services\Messaging\EmailMessagingService;

class WebinarReminderEmailPayload implements EmailMessagePayload
{
    public function __construct(
        public WebinarMessageData $data,
        public string $messageType,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            data: WebinarMessageData::fromArray($payload),
            messageType: $payload['message_type'],
        );
    }

    public function send(EmailMessagingService $emailMessagingService): void
    {
        $emailMessagingService->sendReminder($this->data, $this->messageType);
    }
}