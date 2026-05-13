<?php

namespace App\Messaging\Payloads\Webinars;

use App\Contracts\Messaging\SmsMessagePayload;
use App\Data\WebinarMessageData;
use App\Services\Messaging\SmsMessagingService;

class WebinarFollowUpSmsPayload implements SmsMessagePayload
{
    public function __construct(
        public WebinarMessageData $data,
        public string $followUpType,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            data: WebinarMessageData::fromArray($payload),
            followUpType: $payload['follow_up_type'],
        );
    }

    public function send(SmsMessagingService $smsMessagingService): void
    {
        $smsMessagingService->sendPostWebinarFollowUp($this->data, $this->followUpType);
    }
}