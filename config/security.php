<?php

return [
    'crm_login' => [
        'max_attempts' => env('CRM_LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => env('CRM_LOGIN_DECAY_SECONDS', 60),
    ],
];