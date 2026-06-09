<?php

namespace App\Integrations\Webinars\Zoom;

use App\Contracts\Webinars\WebinarProvider;
use App\Data\Webinars\ProviderRegistrationData;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ZoomWebinarProvider implements WebinarProvider
{
    public function __construct(
        private readonly ZoomWebinarService $zoomWebinarService,
        private readonly ZoomWebhookHandler $zoomWebhookHandler,
    ) {}

    public function key(): string
    {
        return 'zoom';
    }

    public function name(): string
    {
        return 'Zoom';
    }

    public function registerAttendee(Webinar $webinar, WebinarRegistration $registration): ProviderRegistrationData
    {
        $registration->loadMissing('contact');

        $contact = $registration->contact;

        $response = $this->zoomWebinarService->registerAttendee($webinar->external_id, [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);

        return new ProviderRegistrationData(
            provider: $this->key(),
            registrantId: $response['registrant_id'] ?? $response['id'] ?? null,
            joinUrl: $response['join_url'] ?? null,
            raw: $response,
        );
    }

    public function listWebinarsByTitle(string $title): iterable
    {
        return $this->zoomWebinarService->listWebinarsByTitle($title);
    }

    public function handleWebhook(Request $request): Response
    {
        return $this->zoomWebhookHandler->handle($request);
    }
}