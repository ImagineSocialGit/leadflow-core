<?php

namespace App\Support\Webinars;

use App\Models\Webinar;
use Illuminate\Support\Str;

class WebinarPlaybackLinkGenerator
{
    public function forWebinar(Webinar $webinar): string
    {
        if (blank($webinar->playback_token)) {
            $webinar->forceFill([
                'playback_token' => Str::random(48),
            ])->save();
        }

        $path = route('webinar.playback.redirect', [
            'token' => $webinar->playback_token,
        ], false);

        return rtrim(config('app.webinar_url', config('app.url')), '/').$path;
    }
}