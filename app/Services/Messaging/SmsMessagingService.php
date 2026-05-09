<?php

namespace App\Services\Messaging;

use App\Data\WebinarMessageData;
use Twilio\Rest\Client;

class SmsMessagingService
{
    public function __construct(
        protected Client $twilio,
        protected DevMessageSink $devMessageSink,
        protected PhoneNumberNormalizer $phoneNumberNormalizer,
        protected SmsSendGuard $smsSendGuard,
    ) {}

    public function sendRegistrationConfirmation(WebinarMessageData $data): void
    {
        $this->send(
            data: $data,
            kind: 'registration_confirmation',
            message: sprintf(
                "You're registered for %s on %s. Join here: %s",
                $data->webinarTitle,
                $data->formattedStart('M j g:i A'),
                $data->webinarJoinUrl
            )
        );
    }

    public function sendReminder(WebinarMessageData $data, string $messageType): void
    {
        $message = $this->messageForReminder($data, $messageType);

        if (! $message) {
            return;
        }

        $this->send(
            data: $data,
            kind: 'reminder',
            message: $message,
            metadata: [
                'message_type' => $messageType,
            ]
        );
    }

    public function sendPostWebinarFollowUp(WebinarMessageData $data, string $followUpType): void
    {
        $message = $this->messageForPostFollowUp($data, $followUpType);

        if (! $message) {
            return;
        }

        $this->send(
            data: $data,
            kind: 'post_webinar_follow_up',
            message: $message,
            metadata: [
                'follow_up_type' => $followUpType,
            ]
        );
    }

    private function send(
        WebinarMessageData $data,
        string $kind,
        string $message,
        array $metadata = []
    ): void {
        if (! config('sms.enabled')) {
            return;
        }

        if (! $data->leadPhone) {
            return;
        }

        $to = $this->phoneNumberNormalizer->normalize($data->leadPhone);

        if (! $to) {
            return;
        }

        if (! $this->smsSendGuard->allows($data, $to, $message, $kind)) {
            return;
        }

        if (app()->environment('local')) {
            $this->devMessageSink->store('sms', [
                ...$data->toArray(),
                ...$metadata,
                'kind' => $kind,
                'normalized_phone' => $to,
                'message' => $message,
            ]);

            $this->smsSendGuard->record($data, $to, $message, $kind);

            return;
        }

        $this->twilio->messages->create($to, [
            'from' => config('sms.from'),
            'body' => $message,
        ]);

        $this->smsSendGuard->record($data, $to, $message, $kind);
    }

    protected function messageForReminder(WebinarMessageData $data, string $messageType): ?string
    {
        return match ($messageType) {
            'reminder_10d' => sprintf(
                '%s is 10 days away: %s on %s. Join here: %s',
                $data->webinarTitle,
                $data->webinarTitle,
                $data->formattedStart('M j g:i A'),
                $data->webinarJoinUrl
            ),
            'reminder_7d' => sprintf(
                '%s is 1 week away. It starts %s. Join here: %s',
                $data->webinarTitle,
                $data->formattedStart('M j g:i A'),
                $data->webinarJoinUrl
            ),
            'reminder_24h' => sprintf(
                'Reminder: %s is tomorrow at %s. Join here: %s',
                $data->webinarTitle,
                $data->formattedStart('M j g:i A'),
                $data->webinarJoinUrl
            ),
            'reminder_30m' => sprintf(
                '%s starts in 30 minutes at %s. Join here: %s',
                $data->webinarTitle,
                $data->formattedStart('g:i A'),
                $data->webinarJoinUrl
            ),
            'reminder_10m' => sprintf(
                '%s starts in 10 minutes. Join here: %s',
                $data->webinarTitle,
                $data->webinarJoinUrl
            ),
            'late_joiner_5m' => sprintf(
                '%s is live now. Join here: %s',
                $data->webinarTitle,
                $data->webinarJoinUrl
            ),
            default => null,
        };
    }

    protected function messageForPostFollowUp(WebinarMessageData $data, string $followUpType): ?string
    {
        return match ($followUpType) {
            'missed' => sprintf(
                "Sorry we missed you for %s. We'll follow up with next steps soon.",
                $data->webinarTitle
            ),
            'replay' => sprintf(
                "Thanks for joining %s. We'll send your replay and next steps soon.",
                $data->webinarTitle
            ),
            default => null,
        };
    }
}