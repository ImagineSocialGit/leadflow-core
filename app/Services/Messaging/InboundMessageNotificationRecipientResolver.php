<?php

namespace App\Services\Messaging;

use App\Models\Contact;
use App\Models\InboundMessage;
use App\Models\TeamMember;

class InboundMessageNotificationRecipientResolver
{
    /**
     * @return array{team_member: ?TeamMember, fallback_email: ?string, source: string}|null
     */
    public function resolve(InboundMessage $inboundMessage): ?array
    {
        $teamMember = $this->resolveFromContact($inboundMessage);

        if ($teamMember) {
            return $this->teamMemberRecipient($teamMember, 'contact_owner');
        }

        $teamMember = $this->resolveDefaultTeamMember();

        if ($teamMember) {
            return $this->teamMemberRecipient($teamMember, 'default_team_member');
        }

        $fallbackEmail = trim((string) config(
            'messaging.internal_notifications.inbound_replies.fallback_admin_email',
            ''
        ));

        if ($fallbackEmail !== '') {
            return [
                'team_member' => null,
                'fallback_email' => $fallbackEmail,
                'source' => 'fallback_admin_email',
            ];
        }

        return null;
    }

    private function resolveFromContact(InboundMessage $inboundMessage): ?TeamMember
    {
        $sender = $inboundMessage->sender;

        if (! $sender instanceof Contact) {
            return null;
        }

        $assignedTo = trim((string) $sender->assigned_to);

        if ($assignedTo === '') {
            return null;
        }

        return $this->resolveAssignableTeamMember($assignedTo);
    }

    private function resolveAssignableTeamMember(string $assignedTo): ?TeamMember
    {
        $teamMember = TeamMember::query()
            ->with('notificationPreferences')
            ->active()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($assignedTo)])
            ->first();

        if ($teamMember) {
            return $teamMember;
        }

        return TeamMember::query()
            ->with('notificationPreferences')
            ->active()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($assignedTo)])
            ->first();
    }

    private function resolveDefaultTeamMember(): ?TeamMember
    {
        $email = trim((string) config(
            'messaging.internal_notifications.inbound_replies.default_team_member_email',
            ''
        ));

        if ($email === '') {
            return null;
        }

        return TeamMember::query()
            ->with('notificationPreferences')
            ->active()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
            ->first();
    }

    /**
     * @return array{team_member: TeamMember, fallback_email: null, source: string}
     */
    private function teamMemberRecipient(TeamMember $teamMember, string $source): array
    {
        return [
            'team_member' => $teamMember,
            'fallback_email' => null,
            'source' => $source,
        ];
    }
}