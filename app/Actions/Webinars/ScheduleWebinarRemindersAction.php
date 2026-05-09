<?php

namespace App\Actions\Webinars;

use App\Data\WebinarMessageData;
use App\Jobs\Messaging\SendWebinarReminderEmailJob;
use App\Jobs\Messaging\SendWebinarReminderSmsJob;
use App\Models\WebinarRegistration;
use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;

class ScheduleWebinarRemindersAction
{
    public function handle(WebinarRegistration $registration): void
    {
        $data = WebinarMessageData::fromRegistration($registration);

        foreach (config('webinar_messaging.reminders', []) as $reminder) {
            $type = $reminder['type'];
            $channels = $reminder['channels'] ?? [];

            $sendAt = $this->resolveSendAt(
                registration: $registration,
                reminder: $reminder
            );

            if (! $sendAt || $sendAt->isPast()) {
                continue;
            }

            foreach ($channels as $channel) {
                $this->dispatchReminder(
                    channel: $channel,
                    type: $type,
                    data: $data,
                    sendAt: $sendAt
                );
            }
        }
    }

    protected function resolveSendAt(
        WebinarRegistration $registration,
        array $reminder
    ): ?Carbon {
        $webinar = $registration->webinar;
        $timing = $reminder['timing'] ?? [];

        if (($timing['after_registration'] ?? false) === true) {
            return now();
        }

        if (isset($timing['before_start'])) {
            return $webinar->starts_at->copy()->sub(
                CarbonInterval::make($timing['before_start'])
            );
        }

        if (($timing['at_start'] ?? false) === true) {
            return $webinar->starts_at->copy();
        }

        if (isset($timing['after_start'])) {
            return $webinar->starts_at->copy()->add(
                CarbonInterval::make($timing['after_start'])
            );
        }

        if (isset($timing['after_end'])) {
            return $webinar->ends_at->copy()->add(
                CarbonInterval::make($timing['after_end'])
            );
        }

        return null;
    }

    protected function dispatchReminder(
        string $channel,
        string $type,
        WebinarMessageData $data,
        Carbon $sendAt
    ): void {
        if (config('webinar_messaging.testing.enabled')) {
            $sendAt = $this->localTestingSendAt($type);
        }

        match ($channel) {
            'email' => SendWebinarReminderEmailJob::dispatch(
                payload: $data->toArray(),
                messageType: $type,
            )
                ->delay($sendAt)
                ->onQueue('emails'),

            'sms' => SendWebinarReminderSmsJob::dispatch(
                payload: $data->toArray(),
                messageType: $type,
            )
                ->delay($sendAt)
                ->onQueue('notifications'),

            default => null,
        };
    }

    protected function localTestingSendAt(string $type): Carbon
    {
        $reminders = collect(config('webinar_messaging.reminders', []))
            ->pluck('type')
            ->values();

        $index = $reminders->search($type);

        $step = (int) config('webinar_messaging.testing.delay_step_seconds', 60);

        return now()->addSeconds(($index === false ? 1 : $index + 1) * $step);
    }
}