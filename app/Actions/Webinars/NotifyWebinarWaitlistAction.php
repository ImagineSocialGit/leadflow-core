<?php

namespace App\Actions\Webinars;

use App\Jobs\SendWebinarScheduledWaitlistEmailJob;
use App\Jobs\SendWebinarScheduledWaitlistSmsJob;
use App\Models\WebinarSeries;
use App\Models\WebinarWaitlistSignup;

class NotifyWebinarWaitlistAction
{
    public function __construct(
        protected GetNextUpcomingWebinarAction $getNextUpcomingWebinarAction,
    ) {}

    public function execute(WebinarSeries $series): int
    {
        $webinar = $this->getNextUpcomingWebinarAction->getForSeries($series);

        if (! $webinar) {
            return 0;
        }

        $notified = 0;

        WebinarWaitlistSignup::query()
            ->where('webinar_series_id', $series->id)
            ->whereNull('notified_at')
            ->orderBy('id')
            ->chunkById(100, function ($signups) use ($webinar, &$notified): void {
                foreach ($signups as $signup) {
                    if ($signup->email && $signup->email_consent_at) {
                        SendWebinarScheduledWaitlistEmailJob::dispatch($signup->id, $webinar->id);
                    }

                    if ($signup->phone && $signup->sms_consent_at) {
                        SendWebinarScheduledWaitlistSmsJob::dispatch($signup->id, $webinar->id);
                    }

                    $signup->forceFill([
                        'notified_at' => now(),
                    ])->save();

                    $notified++;
                }
            });

        return $notified;
    }
}