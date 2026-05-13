<?php

namespace App\Jobs\Messaging;

use App\Contracts\Messaging\EmailMessagePayload;
use App\Models\WebinarScheduledMessage;
use App\Services\Messaging\EmailMessagingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use InvalidArgumentException;
use Throwable;

class SendEmailMessageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $payloadClass,
        public array $payload,
        public ?int $scheduledMessageId = null,
    ) {}

    public function handle(EmailMessagingService $emailMessagingService): void
    {
        if (! is_subclass_of($this->payloadClass, EmailMessagePayload::class)) {
            throw new InvalidArgumentException("{$this->payloadClass} must implement ".EmailMessagePayload::class);
        }

        $scheduled = $this->scheduledMessageId
            ? WebinarScheduledMessage::query()->find($this->scheduledMessageId)
            : null;

        try {
            $this->payloadClass::fromArray($this->payload)
                ->send($emailMessagingService);

            if ($scheduled) {
                $scheduled->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        } catch (Throwable $e) {
            if ($scheduled) {
                $scheduled->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                ]);
            }

            throw $e;
        }
    }
}