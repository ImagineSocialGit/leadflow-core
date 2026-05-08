<?php

namespace Tests\Feature\Webinars;

use App\Actions\Webinars\ProcessWebinarOutcomeAction;
use App\Jobs\Messaging\SendWebinarMissedYouFollowUpJob;
use App\Jobs\Messaging\SendWebinarReplayFollowUpJob;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessWebinarOutcomeActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_attended_registration_routes_to_replay_follow_up(): void
    {
        Queue::fake();

        $webinar = Webinar::factory()->create();

        $registration = WebinarRegistration::query()->create([
            'webinar_id' => $webinar->id,
            'webinar_slug' => $webinar->slug,
            'status' => 'registered',
            'source' => 'webinar_subdomain',
            'email' => 'attendee@example.com',
            'registered_at' => now(),
            'attended_at' => now(),
        ]);

        app(ProcessWebinarOutcomeAction::class)
            ->execute($registration);

        Queue::assertPushed(
            SendWebinarReplayFollowUpJob::class,
            2
        );

        Queue::assertNotPushed(
            SendWebinarMissedYouFollowUpJob::class
        );
    }

    public function test_non_attendee_routes_to_missed_follow_up(): void
    {
        Queue::fake();

        $webinar = Webinar::factory()->create();

        $registration = WebinarRegistration::query()->create([
            'webinar_id' => $webinar->id,
            'webinar_slug' => $webinar->slug,
            'status' => 'registered',
            'source' => 'webinar_subdomain',
            'email' => 'missed@example.com',
            'registered_at' => now(),
            'attended_at' => null,
        ]);

        app(ProcessWebinarOutcomeAction::class)
            ->execute($registration);

        Queue::assertPushed(
            SendWebinarMissedYouFollowUpJob::class,
            2
        );

        Queue::assertNotPushed(
            SendWebinarReplayFollowUpJob::class
        );
    }
}
