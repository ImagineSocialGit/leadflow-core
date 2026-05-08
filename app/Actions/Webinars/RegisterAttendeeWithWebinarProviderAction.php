<?php

namespace App\Actions\Webinars;

use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Services\Webinars\WebinarProviderManager;

class RegisterAttendeeWithWebinarProviderAction
{
    public function __construct(
        protected WebinarProviderManager $webinarProviderManager,
    ) {}

    public function handle(Webinar $webinar, WebinarRegistration $registration): array
    {
        $providerName = $webinar->platform ?: config('webinars.provider', 'zoom');

        return $this->webinarProviderManager
            ->provider($providerName)
            ->registerAttendee($webinar, $registration);
    }
}