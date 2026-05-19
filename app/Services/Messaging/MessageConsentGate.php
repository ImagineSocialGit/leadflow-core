<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\DB;

class MessageConsentGate
{
    public function canSend(int $leadId, string $channel, string $purpose): bool
    {
        $latestConsentAt = DB::table('message_consents')
            ->where('lead_id', $leadId)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->value('consented_at');

        if (! $latestConsentAt) {
            return false;
        }

        return ! DB::table('consent_revocations')
            ->where('lead_id', $leadId)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->where('revoked_at', '>=', $latestConsentAt)
            ->exists();
    }
}