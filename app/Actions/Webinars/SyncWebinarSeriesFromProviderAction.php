<?php

namespace App\Actions\Webinars;

use App\Models\Webinar;
use App\Models\WebinarSeries;
use App\Services\Zoom\ZoomWebinarService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SyncWebinarSeriesFromProviderAction
{
    public function __construct(
        protected ZoomWebinarService $zoomWebinarService,
    ) {}

    public function execute(WebinarSeries $series): array
    {
        $fetchedWebinars = $this->zoomWebinarService->listWebinarsByTitle($series->title);

        $created = 0;
        $updated = 0;
        $deleted = 0;
        $conflicts = [];

        $provider = config('webinars.provider');

        $fetchedExternalIds = collect($fetchedWebinars)
            ->pluck('external_id')
            ->all();

        foreach ($fetchedWebinars as $fetchedWebinar) {
            $webinar = Webinar::query()->firstOrNew([
                'platform' => $provider,
                'external_id' => $fetchedWebinar['external_id'],
                'series_id' => $series->id,
            ]);

            $webinar->fill([
                'title' => $fetchedWebinar['title'],
                'slug' => $this->makeSlug(
                    title: $fetchedWebinar['title'],
                    startTime: $fetchedWebinar['starts_at'],
                    externalId: $fetchedWebinar['external_id'],
                ),
                'join_url' => $fetchedWebinar['join_url'],
                'registration_url' => $fetchedWebinar['registration_url'],
                'starts_at' => $fetchedWebinar['starts_at'],
                'ends_at' => $fetchedWebinar['ends_at'],
                'timezone' => $fetchedWebinar['timezone'],
                'description' => $fetchedWebinar['description'],
                'meta' => $fetchedWebinar['meta'],
            ]);

            if (! $webinar->exists) {
                $webinar->status = 'scheduled';
                $webinar->provider_settings = null;
            }

            $webinar->save();

            if ($webinar->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $seriesWebinars = $series->webinars()
            ->where('ends_at', '>', now())
            ->orderBy('starts_at')
            ->get();

        $active = $seriesWebinars->firstWhere('status', 'active');
        $earliest = $seriesWebinars->first();

        if ($active && $earliest && $active->id !== $earliest->id) {
            $conflicts[] = [
                'series_id' => $series->id,
                'series' => $series->title,
                'active' => $active->title,
                'expected' => $earliest->title,
            ];
        }

        $missingWebinars = $series->webinars()
            ->where('platform', $provider)
            ->whereNotIn('external_id', $fetchedExternalIds)
            ->get();

        $missing = [];

        foreach ($missingWebinars as $missingWebinar) {
            if ($missingWebinar->status === 'completed') {
                continue;
            }

            $hasRegistrations = $missingWebinar->registrations()->exists();

            if (
                $missingWebinar->status === 'scheduled'
                && ! $hasRegistrations
            ) {
                $missingWebinar->delete();
                $deleted++;

                continue;
            }

            $missing[] = [
                'title' => $missingWebinar->title,
                'status' => $missingWebinar->status,
                'has_registrations' => $hasRegistrations,
            ];
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
            'conflicts' => $conflicts,
            'missing' => $missing,
        ];
    }

    protected function makeSlug(string $title, ?Carbon $startTime, string $externalId): string
    {
        if ($startTime) {
            return Str::slug($title.'-'.$startTime->format('Y-m-d-gia'));
        }

        return Str::slug($title.'-'.$externalId);
    }
}
