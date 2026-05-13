<?php

namespace App\Messaging\Payloads\Webinars;

use App\Contracts\Messaging\EmailMessagePayload;
use App\Data\WebinarMessageData;
use App\Services\Messaging\EmailMessagingService;

class WebinarFollowUpEmailPayload implements EmailMessagePayload
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

    public function send(EmailMessagingService $emailMessagingService): void
    {
        $emailMessagingService->sendPostWebinarFollowUp($this->data, $this->followUpType);
    }
}