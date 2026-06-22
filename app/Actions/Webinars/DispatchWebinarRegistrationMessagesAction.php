<?php

namespace App\Actions\Webinars;

use App\Actions\Messaging\DispatchMessageAction;
use App\Data\WebinarMessageData;
use App\Enums\MessageChannel;
use App\Enums\MessagePurpose;
use App\Models\WebinarRegistration;
use App\Services\Messaging\MessageEligibilityGate;

class DispatchWebinarRegistrationMessagesAction
{
    private const SCOPE = 'webinar';

    public function __construct(
        private readonly DispatchMessageAction $dispatchMessageAction,
        private readonly MessageEligibilityGate $messageEligibilityGate,
    ) {}

    public function handle(WebinarRegistration $registration): void
    {
        $registration->loadMissing([
            'contact',
            'webinar',
            'webinar.webinarSeries',
        ]);

        if (! $registration->contact) {
            return;
        }

        $messageData = WebinarMessageData::fromRegistration($registration)->toArray();

        foreach ([MessageChannel::Email, MessageChannel::Sms] as $channel) {

            if (! $this->messageEligibilityGate->allows(
                contact: $registration->contact,
                channel: $channel,
                purpose: MessagePurpose::Transactional,
                scope: self::SCOPE,
            )) {
                continue;
            }

            $this->dispatchMessageAction->handle(
                recipient: $registration->contact,
                channel: $channel,
                purpose: MessagePurpose::Transactional,
                scope: self::SCOPE,
                dispatchKeys: 'registration_created',
                payload: [
                    'tokens' => $messageData,
                    'context' => [
                        'contact' => $registration->contact->toArray(),
                        'webinar_registration' => $registration->toArray(),
                        'webinar' => $registration->webinar?->toArray() ?? [],
                        'webinar_series' => $registration->webinar?->webinarSeries?->toArray() ?? [],
                    ],
                ],
                context: $registration,
                triggeredAt: $registration->registered_at ?? now(),
                anchor: $registration->webinar?->starts_at,
                meta: [
                    'webinar_registration_id' => $registration->getKey(),
                    'webinar_id' => $registration->webinar_id,
                    'webinar_slug' => $registration->webinar_slug,
                ],
            );
        }
    }
}