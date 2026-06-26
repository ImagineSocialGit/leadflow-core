<?php

namespace App\Modules\Messaging\Services\ContactShow;

use App\Modules\Core\Contracts\Contacts\ContactShowDataProvider;
use App\Modules\Core\Models\Contact;
use App\Modules\Messaging\Models\ConsentRevocation;
use App\Modules\Messaging\Models\MessageConsent;
use App\Modules\Messaging\Models\ScheduledMessage;

class ContactMessagingShowDataProvider implements ContactShowDataProvider
{
    /**
     * @return array<string, mixed>
     */
    public function dataFor(Contact $contact): array
    {
        return [
            'scheduledMessages' => ScheduledMessage::query()
                ->where('recipient_type', $contact->getMorphClass())
                ->where('recipient_id', $contact->id)
                ->where('status', 'sent')
                ->latest('send_at')
                ->paginate(10, ['*'], 'messages_page')
                ->withQueryString(),

            'messageConsents' => MessageConsent::query()
                ->where('contact_id', $contact->id)
                ->get(),

            'consentRevocations' => ConsentRevocation::query()
                ->where('contact_id', $contact->id)
                ->latest('revoked_at')
                ->get(),
        ];
    }
}