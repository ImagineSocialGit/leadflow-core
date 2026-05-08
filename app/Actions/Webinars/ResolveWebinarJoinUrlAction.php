<?php

namespace App\Actions\Webinars;

use App\Models\WebinarRegistration;

class ResolveWebinarJoinUrlAction
{
    public function execute(WebinarRegistration $registration): ?string
    {
        $registration->loadMissing('webinar');

        return data_get($registration->meta, 'provider.join_url')
            ?: $registration->webinar?->join_url;
    }
}