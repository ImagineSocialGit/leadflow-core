<?php

// Shared content

return [

    'brand' => [
        'name' => config('app.name'),
        'logo' => config('generated.images.logo'),
        'logo_alt' => config('app.name'),
    ],

    'footer' => [
        'text' => config('app.name'),
        'compliance_identity' => [
            'enabled' => true,
            'lines' => [
                'Slam Dunk Home Loans',
                'A division of Plum Creek Funding Inc.',
                'NMLS #2449233 / #322537',
            ],
        ],
    ],
];