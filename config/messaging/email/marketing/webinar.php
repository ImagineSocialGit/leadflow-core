<?php

use App\Messaging\Payloads\EmailPayload;

return [

    'opt_in' => [
        'timing' => 'immediate',
        'payload_class' => EmailPayload::class,
        'queue' => 'opt_in_messages',

        'payload' => [
            'subject' => 'You’re subscribed',
            'body' => 'Thanks for subscribing to receive updates. You can unsubscribe at any time.',
        ],
    ],

    'general_message' => [
        'timing' => 'scheduled',
        'payload_class' => EmailPayload::class,
        'queue' => 'marketing',

        'schedule' => [
            'type' => 'delay',
            'minutes' => 0,
        ],

        'payload' => [],
    ],

];