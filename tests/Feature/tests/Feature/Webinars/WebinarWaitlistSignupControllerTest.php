<?php

namespace Tests\Feature\Public;

use App\Models\WebinarSeries;
use App\Models\WebinarWaitlistSignup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebinarWaitlistSignupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_waitlist_signup_is_stored_successfully(): void
    {
        $series = WebinarSeries::factory()->create([
            'status' => 'active',
            'slug' => 'first-time-homebuyer',
        ]);

        $response = $this->post(route('webinar.waitlist.store', $series->slug), [
            'first_name' => 'Jeff',
            'last_name' => 'Yarnall',
            'email' => 'jeff@gmail.com',
            'phone' => '6155551212',
            'consent_messages' => true,
        ]);

        $response
            ->assertRedirect(route('webinar.show', $series->slug));

        $this->assertDatabaseHas('webinar_waitlist_signups', [
            'webinar_series_id' => $series->id,
            'email' => 'jeff@gmail.com',
            'phone' => '6155551212',
        ]);
    }

    public function test_duplicate_waitlist_signup_updates_existing_record(): void
    {
        $series = WebinarSeries::factory()->create([
            'status' => 'active',
        ]);

        $signup = WebinarWaitlistSignup::factory()->create([
            'webinar_series_id' => $series->id,
            'email' => 'jeff@gmail.com',
            'first_name' => 'Old',
        ]);

        $this->post(route('webinar.waitlist.store', $series->slug), [
            'first_name' => 'Jeff',
            'last_name' => 'Yarnall',
            'email' => 'jeff@gmail.com',
            'phone' => '6155551212',
            'consent_messages' => true,
        ]);

        $this->assertDatabaseCount('webinar_waitlist_signups', 1);

        $signup->refresh();

        $this->assertSame('Jeff', $signup->first_name);
        $this->assertSame('6155551212', $signup->phone);
    }

    public function test_waitlist_signup_requires_message_consent(): void
    {
        $series = WebinarSeries::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->from(route('webinar.show', $series->slug))
            ->post(route('webinar.waitlist.store', $series->slug), [
                'first_name' => 'Jeff',
                'last_name' => 'Yarnall',
                'email' => 'jeff@gmail.com',
                'phone' => '6155551212',
                'consent_messages' => false,
            ]);

        $response
            ->assertRedirect(route('webinar.show', $series->slug))
            ->assertSessionHasErrors([
                'consent_messages',
            ]);

        $this->assertDatabaseCount('webinar_waitlist_signups', 0);
    }
}