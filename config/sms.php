<?php

return [

    'enabled' => env('SMS_ENABLED', true),

    'provider' => env('SMS_PROVIDER', 'telnyx'),

    'managed_by' => env('SMS_MANAGED_BY', 'platform'),

    'from' => [
        'transactional' => env('SMS_FROM_TRANSACTIONAL', env('SMS_FROM', env('TWILIO_FROM', env('TELNYX_FROM')))),
        'marketing' => env('SMS_FROM_MARKETING', env('SMS_FROM', env('TWILIO_FROM', env('TELNYX_FROM')))),
    ],

    'providers' => [

        'twilio' => [
            'from' => [
                'transactional' => env('TWILIO_FROM_TRANSACTIONAL', env('SMS_FROM_TRANSACTIONAL', env('SMS_FROM', env('TWILIO_FROM')))),
                'marketing' => env('TWILIO_FROM_MARKETING', env('SMS_FROM_MARKETING', env('SMS_FROM', env('TWILIO_FROM')))),
            ],

            'webhooks' => [
                'stop_keywords' => [
                    'stop',
                    'stopall',
                    'unsubscribe',
                    'cancel',
                    'end',
                    'quit',
                    'revoke',
                    'optout',
                ],

                'start_keywords' => [
                    'start',
                    'yes',
                    'unstop',
                ],

                'help_keywords' => [
                    'help',
                    'info',
                ],

                'stop_response' => 'You have been opted out of SMS messages. Reply START to resubscribe.',
                'help_response' => 'Reply STOP to opt out of SMS messages. Message and data rates may apply.',
            ],
        ],

        'telnyx' => [
            'from' => [
                'transactional' => env('TELNYX_FROM_TRANSACTIONAL', env('SMS_FROM_TRANSACTIONAL', env('TELNYX_FROM', env('SMS_FROM')))),
                'marketing' => env('TELNYX_FROM_MARKETING', env('SMS_FROM_MARKETING', env('TELNYX_FROM', env('SMS_FROM')))),
            ],

            'webhooks' => [
                'stop_keywords' => [
                    'stop',
                    'stopall',
                    'unsubscribe',
                    'cancel',
                    'end',
                    'quit',
                    'revoke',
                    'optout',
                ],

                'start_keywords' => [
                    'start',
                    'yes',
                    'unstop',
                ],

                'help_keywords' => [
                    'help',
                    'info',
                ],

                'stop_response' => 'You have been opted out of SMS messages. Reply START to resubscribe.',
                'help_response' => 'Reply STOP to opt out of SMS messages. Message and data rates may apply.',
            ],
        ],

    ],

    'queues' => [
        'default' => env('SMS_QUEUE', 'sms'),
    ],

    'rate_limits' => [
        'per_ip_per_hour' => env('SMS_RATE_LIMIT_PER_IP_PER_HOUR', 5),
        'per_phone_per_day' => env('SMS_RATE_LIMIT_PER_PHONE_PER_DAY', 10),
    ],

    'cooldowns' => [
        'duplicate_window_minutes' => env('SMS_DUPLICATE_WINDOW_MINUTES', 15),
    ],

    'monitoring' => [
        'daily_send_alert_threshold' => env('SMS_DAILY_ALERT_THRESHOLD', 500),

        'daily_send_hard_limit' => env('SMS_DAILY_HARD_LIMIT', 2000),
    ],

];