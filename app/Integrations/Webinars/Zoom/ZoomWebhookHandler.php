<?php

namespace App\Integrations\Webinars\Zoom;

use App\Data\Webinars\ProviderWebhookEvent;
use Illuminate\Http\Request;

class ZoomWebhookHandler
{
    private const PROVIDER = 'zoom';

    public function __construct(
        private readonly ZoomWebhookVerifier $verifier,
    ) {}

    public function parse(Request $request): ProviderWebhookEvent
    {
        if ($request->input('event') === 'endpoint.url_validation') {
            return new ProviderWebhookEvent(
                provider: self::PROVIDER,
                event: 'endpoint.url_validation',
                payload: [
                    'response' => $this->verifier->urlValidationResponse($request),
                ],
            );
        }

        if (! $this->verifier->hasValidSignature($request)) {
            abort(401);
        }

        return new ProviderWebhookEvent(
            provider: self::PROVIDER,
            event: (string) $request->input('event'),
            externalWebinarId: filled($request->input('payload.object.id'))
                ? (string) $request->input('payload.object.id')
                : null,
            externalWebinarUuid: filled($request->input('payload.object.uuid'))
                ? (string) $request->input('payload.object.uuid')
                : null,
            payload: $request->all(),
        );
    }
}