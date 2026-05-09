<?php

return [

    'reminders' => [

        /*
        |--------------------------------------------------------------------------
        | Registration Confirmation
        |--------------------------------------------------------------------------
        */

        [
            'type' => 'confirmation',
            'channels' => ['email', 'sms'],
            'timing' => [
                'after_registration' => true,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Pre-Webinar Reminders
        |--------------------------------------------------------------------------
        */

        [
            'type' => 'reminder_10d',
            'channels' => ['email', 'sms'],
            'timing' => [
                'before_start' => [
                    'days' => 10,
                ],
            ],
        ],

        [
            'type' => 'reminder_7d',
            'channels' => ['email', 'sms'],
            'timing' => [
                'before_start' => [
                    'days' => 7,
                ],
            ],
        ],

        [
            'type' => 'reminder_24h',
            'channels' => ['email', 'sms'],
            'timing' => [
                'before_start' => [
                    'hours' => 24,
                ],
            ],
        ],

        [
            'type' => 'reminder_30m',
            'channels' => ['email', 'sms'],
            'timing' => [
                'before_start' => [
                    'minutes' => 30,
                ],
            ],
        ],

        [
            'type' => 'reminder_10m',
            'channels' => ['email', 'sms'],
            'timing' => [
                'before_start' => [
                    'minutes' => 10,
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Live Webinar Notifications
        |--------------------------------------------------------------------------
        */

        [
            'type' => 'live',
            'channels' => ['sms'],
            'timing' => [
                'at_start' => true,
            ],
        ],

        [
            'type' => 'late_joiner_5m',
            'channels' => ['sms'],
            'timing' => [
                'after_start' => [
                    'minutes' => 5,
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Post-Webinar Follow-Up
        |--------------------------------------------------------------------------
        */

        [
            'type' => 'post_replay',
            'channels' => ['email', 'sms'],
            'timing' => [
                'after_end' => [
                    'minutes' => 30,
                ],
            ],
            'conditions' => [
                'attendance' => 'attended',
            ],
        ],

        [
            'type' => 'post_missed',
            'channels' => ['email', 'sms'],
            'timing' => [
                'after_end' => [
                    'minutes' => 30,
                ],
            ],
            'conditions' => [
                'attendance' => 'missed',
            ],
        ],
    ],

    'testing' => [
        'enabled' => env('WEBINAR_TEST_SCHEDULING_ENABLED', false),
        'delay_step_seconds' => env('WEBINAR_TEST_DELAY_STEP_SECONDS', 60),
    ],

];
