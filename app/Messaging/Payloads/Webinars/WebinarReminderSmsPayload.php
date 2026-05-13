<?php

namespace App\Messaging\Payloads\Webinars;

use App\Contracts\Messaging\SmsMessagePayload;
use App\Data\WebinarMessageData;
use App\Services\Messaging\SmsMessagingService;

class WebinarReminderSmsPayload implements SmsMessagePayload
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

    public function send(SmsMessagingService $smsMessagingService): void
    {
        $smsMessagingService->sendReminder($this->data, $this->messageType);
    }
}