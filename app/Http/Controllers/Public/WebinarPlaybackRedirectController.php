<?php

namespace App\Http\Controllers\Public;

use App\Actions\Webinars\ResolveWebinarPlaybackUrlAction;
use App\Http\Controllers\Controller;
use App\Models\Webinar;
use Symfony\Component\HttpFoundation\Response;

class WebinarPlaybackRedirectController extends Controller
{
    public function __invoke(
        string $token,
        ResolveWebinarPlaybackUrlAction $resolvePlaybackUrl,
    ) {
        $webinar = Webinar::query()
            ->where('playback_token', $token)
            ->firstOrFail();

        $url = $resolvePlaybackUrl->execute($webinar);

        abort_if(blank($url), Response::HTTP_NOT_FOUND);

        return redirect()->away($url);
    }
}