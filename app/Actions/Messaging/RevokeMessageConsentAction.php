<?php

namespace App\Actions\Messaging;

use App\Rules\Messaging\ConsentRevocationRules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RevokeMessageConsentAction
{
    /**
     * @throws ValidationException
     */
    public function handle(array $data): void
    {
        $validated = Validator::make($data, ConsentRevocationRules::rules())->validate();

        $now = now();

        DB::table('consent_revocations')->insert([
            'lead_id' => $validated['lead_id'],
            'message_consent_id' => $validated['message_consent_id'] ?? null,
            'channel' => $validated['channel'],
            'purpose' => $validated['purpose'],
            'reason' => $validated['reason'],
            'revoked_at' => $validated['revoked_at'] ?? $now,
            'source' => $validated['source'] ?? null,
            'ip_address' => $validated['ip_address'] ?? null,
            'user_agent' => $validated['user_agent'] ?? null,
            'meta' => isset($validated['meta']) ? json_encode($validated['meta']) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}