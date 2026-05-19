<?php

namespace App\Actions\Messaging;

use App\Rules\Messaging\MessageConsentRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GrantMessageConsentAction
{
    /**
     * @throws ValidationException
     */
    public function handle(array $data): void
    {
        $validated = Validator::make($data, MessageConsentRules::rules())->validate();

        $now = now();

        DB::table('message_consents')->updateOrInsert(
            [
                'lead_id' => $validated['lead_id'],
                'channel' => $validated['channel'],
                'purpose' => $validated['purpose'],
            ],
            [
                'webinar_registration_id' => $validated['webinar_registration_id'] ?? null,
                'consented_at' => $validated['consented_at'] ?? $now,
                'ip_address' => $validated['ip_address'] ?? null,
                'user_agent' => $validated['user_agent'] ?? null,
                'source' => $validated['source'] ?? null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}