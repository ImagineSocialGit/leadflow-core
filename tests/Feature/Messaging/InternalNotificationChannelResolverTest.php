<?php

namespace Tests\Feature\Messaging;

use App\Enums\MessageChannel;
use App\Models\TeamMember;
use App\Models\TeamMemberNotificationPreference;
use App\Services\Messaging\InternalNotificationChannelResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalNotificationChannelResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_email_by_default_when_team_member_can_receive_email(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => 'admin@example.com',
            'phone' => '+15551234567',
            'active' => true,
        ]);

        $this->assertSame(
            MessageChannel::Email,
            $this->resolver()->resolve(
                teamMember: $teamMember,
                notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
            )
        );
    }

    public function test_resolves_sms_when_email_is_not_allowed_and_sms_is_enabled(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => null,
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

        $this->assertSame(
            MessageChannel::Sms,
            $this->resolver()->resolve(
                teamMember: $teamMember,
                notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
            )
        );
    }

    public function test_respects_allowed_channel_order(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => 'admin@example.com',
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

        $this->assertSame(
            MessageChannel::Sms,
            $this->resolver()->resolve(
                teamMember: $teamMember,
                notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
                allowedChannels: [MessageChannel::Sms, MessageChannel::Email],
            )
        );
    }

    public function test_respects_allowed_channel_constraint(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => 'admin@example.com',
            'phone' => '+15551234567',
            'active' => true,
        ]);

        $this->assertNull(
            $this->resolver()->resolve(
                teamMember: $teamMember,
                notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
                allowedChannels: [MessageChannel::Sms],
            )
        );
    }

    public function test_returns_null_when_no_allowed_channel_is_eligible(): void
    {
        $teamMember = TeamMember::factory()->inactive()->create([
            'email' => 'admin@example.com',
            'phone' => '+15551234567',
        ]);

        $this->assertNull(
            $this->resolver()->resolve(
                teamMember: $teamMember,
                notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
            )
        );
    }

    public function test_ignores_unknown_string_channels(): void
    {
        $teamMember = TeamMember::factory()->create([
            'email' => 'admin@example.com',
            'active' => true,
        ]);

        $this->assertSame(
            MessageChannel::Email,
            $this->resolver()->resolve(
                teamMember: $teamMember,
                notificationType: TeamMemberNotificationPreference::TYPE_INBOUND_REPLIES,
                allowedChannels: ['fax', 'email'],
            )
        );
    }

    private function resolver(): InternalNotificationChannelResolver
    {
        return app(InternalNotificationChannelResolver::class);
    }
}