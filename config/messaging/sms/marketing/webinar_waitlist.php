<?php

use App\Messaging\Payloads\SmsPayload;

return [

    'webinar_waitlist_scheduled' => [
        'timing' => 'immediate',
        'payload_class' => SmsPayload::class,
        'queue' => 'notifications',

        'payload' => [
            'message' => 'A new webinar has been scheduled for {webinar_title}. Register here: {registration_url}',
        ],
    ],

    'webinar_waitlist_opt_in' => [
        'timing' => 'immediate',
        'payload_class' => SmsPayload::class,
        'queue' => 'opt_in_messages',

        'payload' => [
            'message' => 'Thanks for joining the webinar waitlist. We’ll let you know when a new session is available. Reply STOP to opt out.',
        ],
    ],

];