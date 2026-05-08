<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Webinars\Providers\Zoom\ZoomWebhookHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebinarWebhookController extends Controller
{
    public function __invoke(Request $request, ZoomWebhookHandler $zoomWebhookHandler): Response
    {
        return match (config('webinars.provider', 'zoom')) {
            'zoom' => $zoomWebhookHandler->handle($request),
            default => abort(404),
        };
    }
}
