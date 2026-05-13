<?php

namespace App\Messaging\Payloads\Webinars;

use App\Contracts\Messaging\EmailMessagePayload;
use App\Models\Webinar;
use App\Models\WebinarWaitlistSignup;
use App\Services\Messaging\EmailMessagingService;
use RuntimeException;

class WebinarWaitlistScheduledEmailPayload implements EmailMessagePayload
{
    public function __construct(
        public int $signupId,
        public int $webinarId,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            signupId: $payload['signup_id'],
            webinarId: $payload['webinar_id'],
        );
    }

    public function send(EmailMessagingService $emailMessagingService): void
    {
        $signup = WebinarWaitlistSignup::query()->find($this->signupId);

        $webinar = Webinar::query()
            ->with('series')
            ->find($this->webinarId);

        if (! $signup || ! $webinar) {
            throw new RuntimeException('Waitlist signup or webinar not found.');
        }

        $emailMessagingService->sendWebinarWaitlistScheduledNotification(
            signup: $signup,
            webinar: $webinar,
        );
    }
}