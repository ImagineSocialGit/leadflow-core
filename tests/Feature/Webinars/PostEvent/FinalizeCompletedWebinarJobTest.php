<?php

namespace Tests\Feature\Webinars\PostEvent;

use App\Contracts\Webinars\WebinarProvider;
use App\Data\Webinars\ProviderRecordingData;
use App\Data\Webinars\WebinarAttendanceRecord;
use App\Jobs\Webinars\FinalizeCompletedWebinarJob;
use App\Jobs\Webinars\RoutePostWebinarRegistrationJob;
use App\Models\Contact;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Services\Webinars\WebinarProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class FinalizeCompletedWebinarJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_playback_records_attendance_and_routes_follow_up_messages(): void
    {
        Queue::fake([RoutePostWebinarRegistrationJob::class]);

        Carbon::setTestNow('2026-06-12 12:00:00');

        $webinar = Webinar::factory()->create([
            'platform' => 'zoom',
            'external_id' => '123456789',
            'playback_url' => null,
            'playback_passcode' => null,
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subHour(),
            'meta' => [],
        ]);

        $attendedContact = Contact::factory()->create([
            'email' => 'person@example.com',
        ]);

        $missedContact = Contact::factory()->create([
            'email' => 'missed@example.com',
        ]);

        $attendedRegistration = WebinarRegistration::factory()
            ->for($webinar)
            ->for($attendedContact)
            ->create([
                'attended_at' => null,
                'meta' => [
                    'provider' => [
                        'data' => [
                            'registrant_id' => 'registrant-1',
                        ],
                    ],
                ],
            ]);

        $missedRegistration = WebinarRegistration::factory()
            ->for($webinar)
            ->for($missedContact)
            ->create([
                'attended_at' => null,
                'meta' => [],
            ]);

        $attendanceRecord = new WebinarAttendanceRecord(
            registrantId: 'registrant-1',
            email: 'person@example.com',
            status: 'attended',
            duration: 3600,
            joinTime: now()->subMinutes(55),
            leaveTime: now()->subMinutes(5),
            raw: ['provider_record' => true],
        );

        $provider = $this->mock(WebinarProvider::class, function (MockInterface $mock) use ($webinar, $attendanceRecord) {
            $mock->shouldReceive('key')
                ->twice()
                ->andReturn('zoom');

            $mock->shouldReceive('getRecording')
                ->once()
                ->withArgs(fn (Webinar $passedWebinar) => $passedWebinar->is($webinar))
                ->andReturn(new ProviderRecordingData(
                    playbackUrl: 'https://zoom.example.test/rec/play/abc123',
                    playbackPasscode: 'pass123',
                    raw: ['recording_id' => 'recording-1'],
                ));

            $mock->shouldReceive('listAttendanceRecords')
                ->once()
                ->withArgs(fn (Webinar $passedWebinar) => $passedWebinar->is($webinar))
                ->andReturn(collect([$attendanceRecord]));
        });

        $this->mock(WebinarProviderManager::class, function (MockInterface $mock) use ($provider) {
            $mock->shouldReceive('provider')
                ->once()
                ->with('zoom')
                ->andReturn($provider);
        });

        app(FinalizeCompletedWebinarJob::class, [
            'provider' => 'zoom',
            'externalWebinarId' => '123456789',
        ])->handle(
            webinarProviderManager: app(WebinarProviderManager::class),
            resolveWebinarPlaybackAction: app(\App\Actions\Webinars\PostEvent\ResolveWebinarPlaybackAction::class),
            recordWebinarAttendanceAction: app(\App\Actions\Webinars\PostEvent\RecordWebinarAttendanceAction::class),
        );

        $webinar->refresh();

        $this->assertSame('https://zoom.example.test/rec/play/abc123', $webinar->playback_url);
        $this->assertSame('pass123', $webinar->playback_passcode);
        $this->assertNotNull(data_get($webinar->meta, 'normalized.post_event.playback_resolved_at'));
        $this->assertNotNull(data_get($webinar->meta, 'normalized.post_event.attendance_recorded_at'));

        $attendedRegistration->refresh();
        $missedRegistration->refresh();

        $this->assertNotNull($attendedRegistration->attended_at);
        $this->assertSame('attended', data_get($attendedRegistration->meta, 'attendance.status'));
        $this->assertSame('zoom', data_get($attendedRegistration->meta, 'attendance.provider'));

        $this->assertNull($missedRegistration->attended_at);
        $this->assertSame('missed', data_get($missedRegistration->meta, 'attendance.status'));
        $this->assertSame('zoom', data_get($missedRegistration->meta, 'attendance.provider'));

        Queue::assertPushed(RoutePostWebinarRegistrationJob::class, 2);

        Queue::assertPushed(RoutePostWebinarRegistrationJob::class, fn (RoutePostWebinarRegistrationJob $job) => $job->registrationId === $attendedRegistration->id);
        Queue::assertPushed(RoutePostWebinarRegistrationJob::class, fn (RoutePostWebinarRegistrationJob $job) => $job->registrationId === $missedRegistration->id);
    }
}