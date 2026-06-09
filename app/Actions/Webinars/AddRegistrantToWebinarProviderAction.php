<?php

namespace App\Actions\Webinars;

use App\Data\Webinars\ProviderRegistrationData;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Services\Webinars\WebinarProviderManager;

class AddRegistrantToWebinarProviderAction
{
    public function __construct(
        private readonly WebinarProviderManager $webinarProviderManager,
    ) {}

    public function handle(Webinar $webinar, WebinarRegistration $registration): ProviderRegistrationData
    {
        return $this->webinarProviderManager
            ->provider($webinar->providerKey())
            ->registerAttendee($webinar, $registration);
    }
}
