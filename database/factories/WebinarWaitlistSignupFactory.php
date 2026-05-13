<?php

namespace Database\Factories;

use App\Models\WebinarSeries;
use App\Models\WebinarWaitlistSignup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebinarWaitlistSignup>
 */
class WebinarWaitlistSignupFactory extends Factory
{
    protected $model = WebinarWaitlistSignup::class;

    public function definition(): array
    {
        return [
            'webinar_series_id' => WebinarSeries::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->optional()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->numerify('##########'),
            'email_consent_at' => now(),
            'sms_consent_at' => now(),
            'notified_at' => null,
            'source_page' => 'webinar-notify-me',
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'meta' => [],
        ];
    }
}