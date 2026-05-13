<?php

namespace App\Jobs\Messaging;

use App\Messaging\Payloads\Webinars\WebinarConfirmationEmailPayload;
use App\Messaging\Payloads\Webinars\WebinarConfirmationSmsPayload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DispatchWebinarRegistrationMessagesJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public array $payload,
    ) {}

    public function handle(): void
    {
        SendEmailMessageJob::dispatch(
            payloadClass: WebinarConfirmationEmailPayload::class,
            payload: $this->payload,
        )->onQueue(config('webinars.queues.confirmation_messages'));

        SendSmsMessageJob::dispatch(
            payloadClass: WebinarConfirmationSmsPayload::class,
            payload: $this->payload,
        )->onQueue(config('webinars.queues.confirmation_messages'));
    }
}