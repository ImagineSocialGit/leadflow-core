<?php

namespace App\Contracts\Webinars;

use App\Data\Webinars\ProviderRegistrationData;
use App\Data\Webinars\ProviderRecordingData;
use App\Data\Webinars\ProviderWebhookEvent;
use App\Data\Webinars\WebinarAttendanceRecord;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;

interface WebinarProvider
{
    public function name(): string;

    public function key(): string;

    /**
     * @return iterable<\App\Data\Webinars\ProviderWebinarData>
     */
    public function listWebinarsByTitle(string $title): iterable;

    public function registerAttendee(Webinar $webinar, WebinarRegistration $registration): ProviderRegistrationData;

    public function parseWebhook(Request $request): ProviderWebhookEvent;

    /**
     * @return iterable<WebinarAttendanceRecord>
     */
    public function listAttendanceRecords(Webinar $webinar): iterable;

    public function getRecording(Webinar $webinar): ?ProviderRecordingData;
}