<?php

namespace App\Contracts\Webinars;

use App\Data\Webinars\ProviderRegistrationData;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface WebinarProvider
{
    public function name(): string;

    public function key(): string;

    /**
     * @return iterable<\App\Data\Webinars\ProviderWebinarData>
     */
    public function listWebinarsByTitle(string $title): iterable;

    public function registerAttendee(Webinar $webinar, WebinarRegistration $registration): ProviderRegistrationData;

    public function handleWebhook(Request $request): Response;
}