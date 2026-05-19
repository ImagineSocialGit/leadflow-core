<?php

namespace App\Actions\Webinars;

use App\Data\WebinarMessageData;
use App\Jobs\Messaging\SendEmailMessageJob;
use App\Jobs\Messaging\SendSmsMessageJob;
use App\Messaging\Payloads\Webinars\WebinarFollowUpEmailPayload;
use App\Messaging\Payloads\Webinars\WebinarFollowUpSmsPayload;
use App\Models\WebinarRegistration;
use App\Models\WebinarScheduledMessage;
use App\Services\Messaging\MessageConsentGate;

class ProcessWebinarOutcomeAction
{

    public function __construct(
        protected MessageConsentGate $messageConsentGate,
    ) {}

    public function handle(WebinarRegistration $registration): void
    {
        $registration->loadMissing('webinar');

        if (data_get($registration->meta, 'post_webinar_routed_at')) {
            return;
        }

        if ($registration->attended_at) {
            $this->dispatchFollowUpMessages($registration, 'replay');

            return;
        }

        $this->dispatchFollowUpMessages($registration, 'missed');
    }

    protected function dispatchFollowUpMessages(
        WebinarRegistration $registration,
        string $followUpType
    ): void {
        $meta = $registration->meta ?? [];
        $meta['post_webinar_routed_at'] = now()->toIso8601String();

        $registration->forceFill([
            'meta' => $meta,
        ])->save();

        if ($this->messageConsentGate->canSend(
            leadId: $registration->lead_id,
            channel: 'email',
            purpose: 'transactional',
        )) {
            $this->dispatchEmail($registration, $followUpType);
        }

        if ($this->messageConsentGate->canSend(
            leadId: $registration->lead_id,
            channel: 'sms',
            purpose: 'transactional',
        )) {
            $this->dispatchSms($registration, $followUpType);
        }
    }

    protected function dispatchEmail(
        WebinarRegistration $registration,
        string $followUpType
    ): void {
        $scheduled = WebinarScheduledMessage::query()->firstOrCreate(
            [
                'webinar_registration_id' => $registration->id,
                'channel' => 'email',
                'message_type' => 'post_'.$followUpType,
            ],
            [
                'status' => 'pending',
                'send_at' => now(),
                'meta' => null,
            ]
        );

        if (! $scheduled->wasRecentlyCreated) {
            return;
        }

        SendEmailMessageJob::dispatch(
            payloadClass: WebinarFollowUpEmailPayload::class,
            payload: [
                ...WebinarMessageData::fromRegistration($registration)->toArray(),
                'follow_up_type' => $followUpType,
            ],
            scheduledMessageId: $scheduled->id,
        )->onQueue(config('webinars.queues.followups'));
    }

    protected function dispatchSms(
        WebinarRegistration $registration,
        string $followUpType
    ): void {
        $scheduled = WebinarScheduledMessage::query()->firstOrCreate(
            [
                'webinar_registration_id' => $registration->id,
                'channel' => 'sms',
                'message_type' => 'post_'.$followUpType,
            ],
            [
                'status' => 'pending',
                'send_at' => now(),
                'meta' => null,
            ]
        );

        if (! $scheduled->wasRecentlyCreated) {
            return;
        }

        SendSmsMessageJob::dispatch(
            payloadClass: WebinarFollowUpSmsPayload::class,
            payload: [
                ...WebinarMessageData::fromRegistration($registration)->toArray(),
                'follow_up_type' => $followUpType,
            ],
            scheduledMessageId: $scheduled->id,
        )->onQueue(config('webinars.queues.followups'));
    }
}