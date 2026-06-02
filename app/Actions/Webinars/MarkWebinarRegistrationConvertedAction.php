<?php

namespace App\Actions\Webinars;

use App\Models\WebinarRegistration;
use Carbon\CarbonInterface;

class MarkWebinarRegistrationConvertedAction
{
    public function execute(
        int $contactId,
        ?CarbonInterface $convertedAt = null
    ): ?WebinarRegistration {
        $convertedAt ??= now();

        $registration = WebinarRegistration::query()
            ->where('contact_id', $contactId)
            ->whereNull('converted_at')
            ->orderByDesc('registered_at')
            ->first();

        if (! $registration) {
            return null;
        }

        $registration->update([
            'converted_at' => $convertedAt,
        ]);

        return $registration;
    }
}
