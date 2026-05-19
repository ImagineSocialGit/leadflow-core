<?php

namespace App\Rules\Messaging;

use Illuminate\Validation\Rule;

class MessageConsentRules
{
    public static function rules(): array
    {
        return [
            'lead_id' => ['required', 'integer', 'exists:leads,id'],
            'webinar_registration_id' => ['nullable', 'integer', 'exists:webinar_registrations,id'],

            'channel' => ['required', 'string', Rule::in(['email', 'sms'])],
            'purpose' => ['required', 'string', Rule::in(['transactional', 'marketing'])],

            'consented_at' => ['nullable', 'date'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'user_agent' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:255'],
        ];
    }
}