<?php

namespace App\Actions\Messaging\Inbound;

use App\Contracts\Messaging\InboundMessageHandler;
use App\Enums\MessageChannel;
use App\Mail\InboundMessageNotificationMail;
use App\Models\InboundMessage;
use App\Models\TeamMember;
use App\Models\TeamMemberNotificationPreference;
use App\Services\Messaging\InboundMessageNotificationRecipientResolver;
use App\Services\Messaging\InternalNotificationChannelResolver;
use Illuminate\Support\Facades\Mail;

class NotifyInternalUsersOfInboundMessageAction implements InboundMessageHandler
{
    public function __construct(
        private readonly InboundMessageNotificationRecipientResolver $recipientResolver,
        private readonly InternalNotificationChannelResolver $channelResolver,
    ) {}

    public function handle(InboundMessage $inboundMessage): ?string
    {
        $recipient = $this->recipientResolver->resolve($inboundMessage);

        if ($recipient !== null) {
            $this->notify($inboundMessage, $recipient);
        }

        $inboundMessage->markProcessed();

        return null;
    }

    /**
     * @param array{team_member: ?TeamMember, fallback_email: ?string, source: string} $recipient
     */
    private function notify(InboundMessage $inboundMessage, array $recipient): void
    {
        $teamMember = $recipient['team_member'];
        $fallbackEmail = $recipient['fallback_email'];

        if ($teamMember instanceof TeamMember) {
            $this->notifyTeamMember($inboundMessage, $teamMember, $recipient['source']);

            return;
        }

        if ($fallbackEmail) {
            Mail::to($fallbackEmail)
                ->send(new InboundMessageNotificationMail(
                    inboundMessage: $inboundMessage,
                    recipientSource: $recipient['source'],
                ));
        }
    }

    private function notifyTeamMember(
        InboundMessage $inboundMessage,
        TeamMember $teamMember,
        string $recipientSource,
    ): void {
        $channel = $this->channelResolver->resolve(
            teamMember: $teamMember,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
            allowedChannels: [MessageChannel::Email],
        );

        if ($channel !== MessageChannel::Email) {
            return;
        }

        Mail::to($teamMember->email)
            ->send(new InboundMessageNotificationMail(
                inboundMessage: $inboundMessage,
                recipientSource: $recipientSource,
            ));
    }
}