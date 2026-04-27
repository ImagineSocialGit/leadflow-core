<?php

return [
    'title' => 'You’re Registered',
    'meta_description' => 'Your webinar registration is confirmed. Check your email and phone for access details and reminders.',

    'hero' => [
        'enabled' => true,
        'eyebrow' => 'Registration Confirmed',
        'title' => 'Your Seat Is Confirmed',
        'body' => 'You’re registered for the live class. Your private access details and reminders are on the way.',
    ],

    'next_steps' => [
        'enabled' => true,
        'heading' => 'What Happens Next',
        'items' => [
            [
                'title' => 'Check your email and phone',
                'body' => 'We’ll send your private access details, reminders, and replay information there.',
            ],
            [
                'title' => 'Add the class to your calendar',
                'body' => 'Block the time now so you do not miss the live training.',
            ],
            [
                'title' => 'Show up ready',
                'body' => 'Bring your mortgage questions. This is strategy, not a sales pitch.',
            ],
        ],
    ],

    'event_details' => [
        'enabled' => true,
        'heading' => 'Class Details',
        'items' => [
            [
                'key' => 'date',
                'label' => 'Date',
                'value' => null,
            ],
            [
                'key' => 'time',
                'label' => 'Time',
                'value' => null,
            ],
            [
                'key' => 'location',
                'label' => 'Where',
                'value' => 'Live on Zoom',
            ],
        ],
    ],

    'actions' => [
        [
            'label' => 'Back to Webinars',
            'route' => 'webinar.index',
            'variant' => 'secondary',
        ],
    ],

    'blocks' => [
        'hero',
        'next_steps',
        'event_details',
        'actions',
    ],
];