<?php

namespace App\Http\Controllers\Webhooks;

use App\Actions\Sms\HandleTwilioInboundSmsWebhookAction;
use App\Http\Controllers\Controller;
use App\Services\Messaging\TwilioWebhookVerifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SmsWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        TwilioWebhookVerifier $verifier,
        HandleTwilioInboundSmsWebhookAction $handleTwilioInboundSmsWebhookAction,
    ): Response {
        if (! $verifier->isValid($request)) {
            abort(403);
        }

        return $this->twiml(
            $handleTwilioInboundSmsWebhookAction->handle($request),
        );
    }
}
