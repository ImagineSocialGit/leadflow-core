<?php

use App\Messaging\Payloads\EmailPayload;

return [

    'opt_in' => [
        'dispatch_key' => 'consent_granted',
        'timing' => 'immediate',
        'payload_class' => EmailPayload::class,
        'queue' => 'opt_in_messages',

        'payload' => [
            'subject' => 'You’re subscribed',
            'body' => 'Thanks for subscribing to receive marketing messages! You can unsubscribe at any time.',
        ],
    ],

    'general_message' => [
        'dispatch_key' => 'webinar_ended',
        'campaign_key' => 'webinar_attended',
        'step' => 1,
        'timing' => 'scheduled',
        'payload_class' => EmailPayload::class,
        'queue' => 'marketing',

        'schedule' => [
            'type' => 'delay',
            'minutes' => 720,
        ],

        'payload' => [
            'subject' => 'What held you back?',
            'body' =>
                'You showed up to the class, so I know you’re serious.
                But you didn’t take the next step!
                Which usually means one of 3 things:
                1. You’re not sure if you qualify
                2. You’re not ready yet
                3. Or you’re just not sure what to do next                
                Fair.
                But here’s the truth:
                You don’t need to have it all figured out. You just need a starting point.
                👉 Fill this out — I’ll take a look and tell you exactly where you stand
                (No pressure. No commitment.)
                -Stacey'
        ],
    ],

];