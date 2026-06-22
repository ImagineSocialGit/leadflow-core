<?php

namespace App\Services\Messaging;

use App\Enums\MessageChannel;
use App\Models\Contact;
use App\Models\ScheduledMessage;
use App\Models\TeamMember;
use App\Services\ConditionChecker;

class ScheduledMessageGate
{
    public function __construct(
        private readonly ConditionChecker $conditionChecker,
        private readonly MessageEligibilityGate $messageEligibilityGate,
        private readonly MessageRecipientPayloadResolver $payloadResolver,
    ) {}

    public function denialReason(ScheduledMessage $scheduledMessage): ?string
    {
        $recipient = $scheduledMessage->recipient;

        if (! $recipient) {
            return 'Recipient not found.';
        }

        if (! $this->definitionStillEnabled($scheduledMessage)) {
            return 'Message definition is disabled or missing.';
        }

        $payload = $scheduledMessage->payload ?? [];

        if (! $this->hasDestination($payload)) {
            return 'Message destination is missing.';
        }

        if (! $this->conditionChecker->passes(
            conditions: $scheduledMessage->meta['conditions'] ?? [],
            context: $this->payloadResolver->conditionContext(
                recipient: $recipient,
                context: $scheduledMessage->context,
                payload: $payload,
            ),
        )) {
            return 'Message conditions no longer pass.';
        }

        if ($recipient instanceof Contact) {
            if (! $this->messageEligibilityGate->allows(
                contact: $recipient,
                channel: $scheduledMessage->channel,
                purpose: $scheduledMessage->purpose,
                scope: $scheduledMessage->scope,
                messageKey: $scheduledMessage->message_type,
                definitionConfigPath: $scheduledMessage->meta['definition_config_path'] ?? null,
            )) {
                return 'Message eligibility gate denied send.';
            }

            return null;
        }

        if ($recipient instanceof TeamMember) {
            if (! $this->teamMemberAllows($scheduledMessage, $recipient)) {
                return 'Team member notification preference denied send.';
            }

            return null;
        }

        return null;
    }

    private function definitionStillEnabled(ScheduledMessage $scheduledMessage): bool
    {
        $configPath = $scheduledMessage->meta['definition_config_path'] ?? null;

        if (! is_string($configPath) || trim($configPath) === '') {
            return true;
        }

        $definition = config($configPath);

        if (! is_array($definition)) {
            return false;
        }

        return (bool) ($definition['enabled'] ?? true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hasDestination(array $payload): bool
    {
        return is_string($payload['to'] ?? null)
            && trim($payload['to']) !== '';
    }

    private function teamMemberAllows(
        ScheduledMessage $scheduledMessage,
        TeamMember $teamMember,
    ): bool {
        $notificationType = $this->notificationType($scheduledMessage);

        return match ($scheduledMessage->channel) {
            MessageChannel::Email->value => $teamMember->canReceiveEmailNotifications($notificationType),
            MessageChannel::Sms->value => $teamMember->canReceiveSmsNotifications($notificationType),
            default => false,
        };
    }

    private function notificationType(ScheduledMessage $scheduledMessage): ?string
    {
        $notificationType = $scheduledMessage->meta['notification_type']
            ?? $scheduledMessage->message_type;

        return is_string($notificationType) && trim($notificationType) !== ''
            ? trim($notificationType)
            : null;
    }
}