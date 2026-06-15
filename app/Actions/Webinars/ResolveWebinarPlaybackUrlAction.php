<?php

namespace App\Actions\Webinars;

use App\Models\Webinar;

class ResolveWebinarPlaybackUrlAction
{
    public function execute(Webinar $webinar): ?string
    {
        return $webinar->playback_url;
    }
}