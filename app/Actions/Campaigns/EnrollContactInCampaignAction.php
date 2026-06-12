<?php

namespace App\Actions\Campaigns;

use App\Actions\Messaging\DispatchMessageAction;
use App\Models\CampaignEnrollment;
use App\Models\Contact;
use App\Models\ScheduledMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EnrollContactInCampaignAction
{
    public function __construct(
        private readonly DispatchMessageAction $dispatchMessageAction,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        Contact $contact,
        string $campaignKey,
        string $channel,
        string $purpose,
        string $scope,
        string $dispatchKey,
        ?Model $source = null,
        array $payload = [],
        ?array $meta = null,
    ): CampaignEnrollment {
        $enrollment = CampaignEnrollment::query()
            ->where('contact_id', $contact->id)
            ->where('campaign_key', $campaignKey)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->where('scope', $scope)
            ->whereIn('status', [
                CampaignEnrollment::STATUS_ACTIVE,
                CampaignEnrollment::STATUS_PAUSED,
            ])
            ->first();

        if ($enrollment instanceof CampaignEnrollment) {
            return $enrollment;
        }

        $enrollment = CampaignEnrollment::create([
            'contact_id' => $contact->id,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'campaign_key' => $campaignKey,
            'channel' => $channel,
            'purpose' => $purpose,
            'scope' => $scope,
            'status' => CampaignEnrollment::STATUS_ACTIVE,
            'current_step' => 0,
            'started_at' => Carbon::now(),
            'meta' => $meta,
        ]);

        $scheduledMessage = $this->scheduleFirstStep(
            enrollment: $enrollment,
            contact: $contact,
            dispatchKey: $dispatchKey,
            source: $source,
            payload: $payload,
            meta: $meta,
        );

        if (! $scheduledMessage instanceof ScheduledMessage) {
            $enrollment->forceFill([
                'status' => CampaignEnrollment::STATUS_COMPLETED,
                'completed_at' => Carbon::now(),
            ])->save();

            return $enrollment;
        }

        $enrollment->forceFill([
            'current_step' => 1,
            'last_scheduled_message_id' => $scheduledMessage->id,
        ])->save();

        return $enrollment;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>|null  $meta
     */
    private function scheduleFirstStep(
        CampaignEnrollment $enrollment,
        Contact $contact,
        string $dispatchKey,
        ?Model $source,
        array $payload,
        ?array $meta,
    ): ?ScheduledMessage {
        $scheduledMessages = $this->dispatchMessageAction->handle(
            contact: $contact,
            channel: $enrollment->channel,
            purpose: $enrollment->purpose,
            scope: $enrollment->scope,
            dispatchKeys: $dispatchKey,
            payload: $payload,
            context: $source,
            meta: array_merge([
                'campaign_enrollment_id' => $enrollment->id,
                'campaign_key' => $enrollment->campaign_key,
                'campaign_step' => 1,
            ], $meta ?? []),
            criteria: [
                'campaign_key' => $enrollment->campaign_key,
                'step' => 1,
            ],
        );

        return $scheduledMessages[0] ?? null;
    }
}