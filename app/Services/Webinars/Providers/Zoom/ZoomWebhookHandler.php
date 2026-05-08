<?php

namespace App\Services\Webinars\Providers\Zoom;

use App\Actions\Webinars\RecordZoomAttendanceAction;
use App\Services\Zoom\ZoomWebinarService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ZoomWebhookHandler
{
    public function __construct(
        protected ZoomWebhookVerifier $verifier,
        protected ZoomWebinarService $zoomWebinarService,
        protected RecordZoomAttendanceAction $recordZoomAttendanceAction,
    ) {}

    public function handle(Request $request): Response
    {
        if ($request->input('event') === 'endpoint.url_validation') {
            return response()->json(
                $this->verifier->urlValidationResponse($request)
            );
        }

        if (! $this->verifier->hasValidSignature($request)) {
            abort(401);
        }

        $event = $request->input('event');

        if (! in_array($event, [
            'webinar.ended',
            'webinar.completed',
        ], true)) {
            return response()->noContent();
        }

        $webinarId = (string) ($request->input('payload.object.id') ?? '');

        if ($webinarId === '') {
            return response()->noContent();
        }

        $participants = $this->zoomWebinarService
            ->listPastWebinarParticipants($webinarId);

        $this->recordZoomAttendanceAction->execute(
            $webinarId,
            $participants
        );

        return response()->noContent();
    }
}