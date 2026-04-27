<?php

// Registered content

return [

    'title' => 'Register for Webinar',
    'meta_description' => 'Reserve your spot for this live online mortgage strategy class.',

    'header' => [
        'primary_link' => [
            'label' => 'Reserve Spot',
            'route' => 'webinar.index',
        ],
    ],

    'series_overrides' => [
        'eyebrow' => true,
        'hero_title' => true,
        'hero_body' => true,
        'urgency_stats' => true,
        'event_details' => true,
        'primary_cta' => true,
        'instructor' => true,
        'secondary_cta' => true,
        'trust' => true,
        'hero_image' => true,
    ],

    'hero' => [
        'enabled' => true,
        'eyebrow' => 'Live Online Class',
        'title_prefix' => null,
        'title' => 'Last Year, 80,000 Pre-Approved Buyers Were Denied After Going Under Contract',
        'body' => 'Most buyers think they’re approved… until the home slips away',
        'closing_copy' => 'I’ll Show You How to Avoid This—And Make Sure You Get Your Home',
        'image' => null,
    ],

    'urgency_stats' => [
        'enabled' => true,
        'intro' => 'Last year:',
        'items' => [
            [
                'value' => '400,000',
                'label' => 'contracts fell apart',
            ],
            [
                'value' => '280,000',
                'label' => 'buyers loans WERE DENIED',
            ],
            [
                'value' => '80,000',
                'label' => 'DENIALS WERE AFTER PRE-APPROVAL, CONTRACT AND MONEY SPENT',
            ],
        ],
    ],

    'primary_cta' => [
        'enabled' => true,
        'pretext' => 'This Is Live. Seats Are Limited.',
        'label' => 'Lock In My Spot Now',
        'mobile_label' => 'Lock In My Spot Now',
        'desktop_header_label' => 'Lock In My Spot Now',
        'route' => 'webinar.show',
        'helper_text' => 'We don’t pack the room. Small group. Real strategy. When it fills, it’s closed.',
    ],

    'countdown' => [
        'enabled' => true,
        'label' => 'Next class starts in:',
        'items' => [
            [
                'method' => 'days',
                'label' => 'Days',
            ],
            [
                'method' => 'hours',
                'label' => 'Hrs',
            ],
            [
                'method' => 'minutes',
                'label' => 'Min',
            ],
            [
                'method' => 'seconds',
                'label' => 'Sec',
            ],
        ],
    ],

    'event_details' => [
        'enabled' => true,
        'heading' => null,
        'items' => [
            [
                'key' => 'date',
                'label' => 'Date',
                'value' => null,
                'icon' => 'calendar',
            ],
            [
                'key' => 'time',
                'label' => 'Time',
                'value' => null,
                'icon' => 'clock',
            ],
            [
                'key' => 'location',
                'label' => 'Where',
                'value' => 'Live on Zoom',
                'icon' => 'map_pin',
            ],
        ],
    ],

    'problem' => [
        'enabled' => true,
        'eyebrow' => 'Where Deals Actually Fall Apart',
        'heading' => 'Pre-approval is not the finish line. It’s the starting point.',
        'body' => [
            'Most buyers don’t lose their deal because they couldn’t qualify.',
            'They lose it because the pre-approval wasn’t done correctly. ',
            'A lot of pre-approvals are issued without actually reviewing everything upfront.',
            'So things get missed.',
            'And buyers move forward with a false sense of security.',
            'By the time the problem shows up…',
            'You’re already under contract.',
            'That’s when everything falls apart.',
        ],
        'bullets' => [
            'Income not structured correctly',
            'Assets not sourced properly',
            'Credit not fully reviewed',
            'Underwriting issues discovered too late',
        ],
    ],

    'instructor' => [
        'enabled' => true,
        'image' => config('generated.images.instructor'),
        'image_alt' => 'Stacey Sandlin and family',
        'image_sizes' => '(min-width: 1024px) 34vw, 90vw',
        'image_caption' => 'A 3-generation mortgage family. We don’t guess — we underwrite upfront.',
        'eyebrow' => 'Why This Doesn’t Happen to My Clients',
        'heading' => 'I don’t guess.',
        'body' => [
            'I’ve spent 30 years inside this process — including underwriting loans before they ever get approved.',
            'I’ve seen exactly where deals break, and I structure them so they don’t.',
            'Most lenders don’t fully underwrite upfront. That’s where problems start.',
        ],
        'credibility' => [
            '30 years in lending',
            'Former underwriter',
            '$1 Billion+ funded',
            'Known for saving deals other lenders can’t',
        ],
    ],

    'form_card' => [
        'enabled' => true,
        'title' => 'Save Your Seat',
        'body' => 'Enter your information below to register for this session.',
    ],

    'fields' => [
        'first_name' => [
            'label' => 'First Name',
            'placeholder' => 'Enter your first name',
        ],
        'last_name' => [
            'label' => 'Last Name',
            'placeholder' => 'Enter your last name',
        ],
        'email' => [
            'label' => 'Email Address',
            'placeholder' => 'Enter your email address',
        ],
        'phone' => [
            'label' => 'Phone Number',
            'placeholder' => 'Enter your phone number',
        ],
        'sms_reminder' => [
            'label' => 'Text me the reminder',
        ],
        'consent_messages' => [
            'label' => 'I agree to receive automated email and text messages from Slam Dunk Home Loans related to webinar registration, including private access details, class reminders, replay access, and follow-up communications. Message frequency varies. Message and data rates may apply. Reply STOP to opt out or HELP for help. Consent is not a condition of purchase.',
        ],
    ],

    'submit' => [
        'label' => 'Reserve My Spot',
    ],

    'secondary_cta' => [
        'enabled' => true,
        'headline' => 'This Is Live. Seats Are Limited.',
        'label' => 'Lock In My Spot Now',
        'route' => 'webinar.show',
        'helper_text' => 'We don’t pack the room. Small group. Real strategy. When it fills, it’s closed.',
    ],

    'trust' => [
        'enabled' => true,
        'headline' => '5.0 Rating — Trusted by Buyers Who Actually Closed',
        'body' => 'Real buyers. Real deals. Real problems caught before they became disasters.',
        'review_label' => 'View Reviews',
        'review_url' => null,
        'reviews' => [
            [
                'name' => 'Daniel Kerbs',
                'stars' => '★★★★★',
                'text' => 'Stacey and her team were incredibly helpful and went above and beyond to help me get the refinance loan I needed to get moving on the next chapter of my life.',
            ],
            [
                'name' => 'Shawna Newberry',
                'stars' => '★★★★★',
                'text' => 'From pre-approval to closing, Stacey keeps everything moving seamlessly.',
            ],
            [
                'name' => 'Luca Pretolesi',
                'stars' => '★★★★★',
                'text' => 'Stacy was absolutely phenomenal. Her speed, precision, and attention to detail were truly impressive, and she made the entire lending process smooth, clear, and stress-free.',
            ],
        ],
    ],

    'final_close' => [
        'enabled' => true,
        'headline' => 'This Is Live. Seats Are Limited.',
        'body' => 'We don’t pack the room. Small group. Real strategy. When it fills, it’s closed.',
        'bullets' => [
            'You’ve made an offer',
            'You’re under contract',
            'You’ve spent money',
            'You’re planning your move',
        ],
        'closing_copy' => 'Not because you couldn’t buy. Because it wasn’t set up right from the beginning.',
        'label' => 'Lock In My Spot Now',
        'helper_text' => null,
        'countdown' => [
            'enabled' => false,
        ],
    ],

    'exit_intent' => [
        'enabled' => true,
        'headline' => 'Wait — before you go… this could save your deal',
        'label' => 'Let Me In',
    ],

    'compliance' => [
        'enabled' => true,
        'text' => 'This is an educational class designed to help consumers better understand the homebuying and mortgage process. No application is required to attend. Loan approval is subject to credit, income, assets, and underwriting guidelines.',
    ],

   'sticky_desktop' => [
        'enabled' => true,
        'label' => 'Lock In My Spot Now',
        'eyebrow' => 'This Is Live. Seats Are Limited.',
    ],

    'sticky_mobile' => [
        'label' => 'Lock In My Spot Now',
    ],

    'legal_links' => [
        'enabled' => true,
        'text' => 'By registering, you agree to our Terms & Conditions and Privacy Policy.',
        'links' => [
            [
                'label' => 'Terms & Conditions',
                'url' => 'https://slamdunkhomeloans.com/terms-and-conditions/',
            ],
            [
                'label' => 'Privacy Policy',
                'url' => 'https://slamdunkhomeloans.com/privacy-policy/',
            ],
        ],
    ],

    'blocks' => [
        'hero',
        'urgency_stats',
        'primary_cta',
        'event_details',
        'problem',
        'instructor',
        'form_card',
        'secondary_cta',
        'trust',
        'final_close',
        'compliance',
    ],


];