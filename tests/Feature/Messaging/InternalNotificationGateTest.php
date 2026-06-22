<?php

namespace Tests\Feature\Messaging;

use App\Enums\MessageChannel;
use App\Models\TeamMember;
use App\Models\TeamMemberNotificationPreference;
use App\Services\Messaging\InternalNotificationGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalNotificationGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_team_member_with_email_is_allowed_email_notifications_by_default(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => 'admin@example.com',
            'active' => true,
        ]);

        $this->assertTrue($this->gate()->allows(
            teamMember: $teamMember,
            channel: MessageChannel::Email,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    public function test_inactive_team_member_is_denied_email_notifications(): void
    {
        $teamMember = TeamMember::factory()->inactive()->create([
            'email' => 'admin@example.com',
        ]);

        $this->assertFalse($this->gate()->allows(
            teamMember: $teamMember,
            channel: MessageChannel::Email,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    public function test_team_member_without_email_is_denied_email_notifications(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => null,
            'active' => true,
        ]);

        $this->assertFalse($this->gate()->allows(
            teamMember: $teamMember,
            channel: MessageChannel::Email,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    public function test_disabled_email_preference_denies_email_notifications(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => 'admin@example.com',
            'active' => true,
        ]);

        TeamMemberNotificationPreference::factory()
            ->for($teamMember)
            ->email()
            ->inboundReplies()
            ->disabled()
            ->create();

        $this->assertFalse($this->gate()->allows(
            teamMember: $teamMember,
            channel: MessageChannel::Email,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    public function test_active_team_member_with_phone_is_denied_sms_notifications_by_default(): void
    {
        $teamMember = TeamMember::factory()->create([
            'phone' => '+15551234567',
            'active' => true,
        ]);

        $this->assertFalse($this->gate()->allows(
            teamMember: $teamMember,
            channel: MessageChannel::Sms,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    public function test_enabled_sms_preference_allows_sms_notifications(): void
    {
        $teamMember = TeamMember::factory()->create([
            'phone' => '+15551234567',
            'active' => true,
        ]);

        TeamMemberNotificationPreference::factory()
            ->for($teamMember)
            ->sms()
            ->inboundReplies()
            ->create([
                'enabled' => true,
            ]);

        $this->assertTrue($this->gate()->allows(
            teamMember: $teamMember,
            channel: MessageChannel::Sms,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    public function test_team_member_without_phone_is_denied_sms_notifications(): void
    {
        $teamMember = TeamMember::factory()->create([
            'phone' => null,
            'active' => true,
        ]);

        TeamMemberNotificationPreference::factory()
            ->for($teamMember)
            ->sms()
            ->inboundReplies()
            ->create([
                'enabled' => true,
            ]);

        $this->assertFalse($this->gate()->allows(
            teamMember: $teamMember,
            channel: MessageChannel::Sms,
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    public function test_unknown_channel_is_denied(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => 'admin@example.com',
            'active' => true,
        ]);

        $this->assertFalse($this->gate()->allows(
            teamMember: $teamMember,
            channel: 'fax',
            notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
        ));
    }

    private function gate(): InternalNotificationGate
    {
        return app(InternalNotificationGate::class);
    }
}