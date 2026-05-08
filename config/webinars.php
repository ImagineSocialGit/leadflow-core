<?php

return [

    'enabled' => env('WEBINARS_ENABLED', true),

    'provider' => env('WEBINAR_PROVIDER', 'zoom'),

    'managed_by' => env('WEBINAR_MANAGED_BY', 'client'),

    'queues' => [

        'registrations' => env('WEBINAR_REGISTRATION_QUEUE', 'webinars'),

        'webhooks' => env('WEBINAR_WEBHOOK_QUEUE', 'webhooks'),

        'reminders' => env('WEBINAR_REMINDER_QUEUE', 'notifications'),
    ],

    'reminders' => [

        'enabled' => true,

        'schedule' => [
            '10_days',
            '7_days',
            '24_hours',
            '30_minutes',
            '10_minutes',
        ],
    ],

    'registration' => [

        'require_unique_email_per_webinar' => true,

        'cooldowns' => [
            'per_email_minutes' => 15,
            'per_phone_minutes' => 15,
            'per_ip_minutes' => 15,
        ],
    ],

    'webhooks' => [

        'validate_signature' => true,

        'max_timestamp_drift_seconds' => 300,

        'replay_cache_ttl_seconds' => 600,
    ],

    'cache' => [

        'next_webinar_ttl_seconds' => 300,

        'webinar_page_ttl_seconds' => 300,
    ],

];