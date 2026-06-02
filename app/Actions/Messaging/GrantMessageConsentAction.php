<?php

namespace App\Actions\Messaging;

use App\Models\MessageConsent;
use App\Rules\Messaging\MessageConsentRules;
use App\Models\Contact;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GrantMessageConsentAction
{
    /**
     * @throws ValidationException
     */
    public function handle(Contact $contact, array $data): MessageConsent
    {
        $validated = Validator::make($data, MessageConsentRules::rules())->validate();

        return MessageConsent::query()->updateOrCreate(
            [
                'contact_id' => $contact->getKey(),
                'channel' => $validated['channel'],
                'purpose' => $validated['purpose'],
            ],
            [
                'consented_at' => $validated['consented_at'] ?? now(),
                'ip_address' => $validated['ip_address'] ?? null,
                'user_agent' => $validated['user_agent'] ?? null,
                'source' => $validated['source'] ?? null,
                'meta' => $validated['meta'] ?? null,
            ]
        );
    }
}