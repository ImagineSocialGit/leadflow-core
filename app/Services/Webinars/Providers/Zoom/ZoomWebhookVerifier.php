<?php

namespace App\Services\Webinars\Providers\Zoom;

use Illuminate\Http\Request;

class ZoomWebhookVerifier
{
    public function urlValidationResponse(Request $request): array
    {
        $plainToken = (string) $request->input('payload.plainToken');

        return [
            'plainToken' => $plainToken,
            'encryptedToken' => hash_hmac(
                'sha256',
                $plainToken,
                (string) config('services.zoom.webhook_secret')
            ),
        ];
    }

    public function hasValidSignature(Request $request): bool
    {
        $secret = config('services.zoom.webhook_secret');

        if (! filled($secret)) {
            return false;
        }

        $timestamp = (string) $request->header('x-zm-request-timestamp');
        $signature = (string) $request->header('x-zm-signature');

        if ($timestamp === '' || $signature === '') {
            return false;
        }

        $message = 'v0:'.$timestamp.':'.$request->getContent();

        $expected = 'v0='.hash_hmac(
            'sha256',
            $message,
            $secret
        );

        return hash_equals($expected, $signature);
    }
}
