<?php

namespace App\Contracts\Webinars;

use App\Models\Webinar;
use App\Models\WebinarRegistration;

interface WebinarProvider
{
    public function name(): string;

    public function registerAttendee(Webinar $webinar, WebinarRegistration $registration): array;
}
