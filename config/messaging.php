<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-channel messaging behavior
    |--------------------------------------------------------------------------
    */

    'recipient_models' => [
        App\Models\Contact::class,
    ],

    'consent' => [
        'require_active_consent' => true,
    ],

    'suppression' => [
        'enabled' => true,
    ],

    'scheduling' => [
        'dedupe_enabled' => true,
    ],

    'internal_notifications' => [
        'inbound_replies' => [
            'default_team_member_email' => env('INBOUND_REPLY_DEFAULT_TEAM_MEMBER_EMAIL'),
            'fallback_admin_email' => env('INBOUND_REPLY_FALLBACK_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),
        ],
    ],

    'inbound' => [
        'handlers' => [
            'sms' => [
                'consent_revocation' => [
                    App\Actions\Messaging\Sms\Inbound\RevokeSmsConsentFromInboundMessageAction::class,
                ],

                'help' => [
                    App\Actions\Messaging\Sms\Inbound\RespondToSmsHelpInboundMessageAction::class,
                ],

                'normal_reply' => [
                    App\Actions\Messaging\Inbound\NotifyInternalUsersOfInboundMessageAction::class,
                ],
            ],
        ],
    ],

];