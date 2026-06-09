<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Webinars\WebinarProviderManager;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class WebinarWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        WebinarProviderManager $webinarProviderManager,
        ?string $provider = null,
    ): Response {
        try {
            return $webinarProviderManager
                ->provider($provider)
                ->handleWebhook($request);
        } catch (InvalidArgumentException) {
            abort(404);
        }
    }
}