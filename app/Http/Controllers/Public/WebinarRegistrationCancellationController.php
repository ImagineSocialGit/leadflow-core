<?php

namespace App\Http\Controllers\Public;

use App\Actions\Webinars\CancelWebinarRegistrationAction;
use App\Http\Controllers\Controller;
use App\Models\WebinarRegistration;
use Illuminate\Http\Response;

class WebinarRegistrationCancellationController extends Controller
{
    public function __invoke(
        WebinarRegistration $registration,
        CancelWebinarRegistrationAction $cancelWebinarRegistrationAction
    ): Response {
        $registration = $cancelWebinarRegistrationAction->handle($registration);

        return response()->view('webinar.registration-cancelled', [
            'registration' => $registration,
        ]);
    }
}