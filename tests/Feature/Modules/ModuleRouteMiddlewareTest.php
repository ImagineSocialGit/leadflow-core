<?php

namespace Tests\Feature\Modules;

use App\Http\Middleware\ForceStagingAccess;
use App\Models\User;
use Tests\TestCase;

class ModuleRouteMiddlewareTest extends TestCase
{
    public function test_disabled_webinar_module_returns_404_for_crm_webinar_routes(): void
    {
        config()->set('modules.enabled', [
            'messaging',
            'inbound_messaging',
            'internal_notifications',
            'tasks',
            'campaigns',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withoutMiddleware(ForceStagingAccess::class)
            ->get('http://crm.'.config('app.root_domain').'/webinars')
            ->assertNotFound();
    }

    public function test_enabled_webinar_module_allows_crm_webinar_routes(): void
    {
        config()->set('modules.enabled', [
            'messaging',
            'inbound_messaging',
            'internal_notifications',
            'tasks',
            'campaigns',
            'webinars',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withoutMiddleware(ForceStagingAccess::class)
            ->get('http://crm.'.config('app.root_domain').'/webinars')
            ->assertOk();
    }

    public function test_disabled_inbound_messaging_module_returns_404_for_sms_webhook(): void
    {
        config()->set('modules.enabled', [
            'messaging',
            'internal_notifications',
            'tasks',
            'campaigns',
            'webinars',
        ]);

        $this->withoutMiddleware(\App\Http\Middleware\ForceStagingAccess::class);

        $response = $this->post('http://webhooks.'.config('app.root_domain').'/sms/telnyx');

        $response->assertNotFound();
    }
}