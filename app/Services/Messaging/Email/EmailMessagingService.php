<?php

namespace App\Services\Messaging\Email;

use App\Contracts\Messaging\Email\EmailMessagePayload;
use App\Services\Messaging\DevMessageSink;
use Illuminate\Support\Facades\Mail;

class EmailMessagingService
{
    public function __construct(
        private readonly DevMessageSink $devMessageSink,
    ) {}

    public function send(EmailMessagePayload $payload): void
    {
        if (! $payload->to()) {
            return;
        }

        if (app()->environment('local')) {
            $this->devMessageSink->store('email', $payload->devPayload());

            return;
        }

        Mail::to($payload->to())->send($payload->mailable());
    }
}