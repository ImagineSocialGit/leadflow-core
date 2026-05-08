<?php

namespace Database\Factories;

use App\Models\Webinar;
use App\Models\WebinarSeries;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class WebinarFactory extends Factory
{
    protected $model = Webinar::class;

    public function definition(): array
    {
        $startsAt = Carbon::now()->addDays(7);

        return [
            'title' => 'Test Webinar',
            'slug' => 'test-webinar-'.Str::lower(Str::random(6)),
            'series_id' => WebinarSeries::query()->create([
                'title' => 'Test Series',
                'slug' => 'test-series-'.Str::lower(Str::random(6)),
                'status' => 'active',
            ])->id,
            'platform' => 'zoom',
            'external_id' => (string) fake()->numerify('#########'),
            'status' => 'scheduled',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
            'timezone' => 'America/Chicago',
            'description' => 'Factory webinar',
            'meta' => [],
        ];
    }

    public function completed(): static
    {
        return $this->state(function () {
            $startsAt = Carbon::now()->subHours(2);

            return [
                'status' => 'completed',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHour(),
            ];
        });
    }
}
