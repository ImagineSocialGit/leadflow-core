<?php

namespace App\Actions\Webinars\PostEvent;

use App\Data\Webinars\ProviderWebhookEvent;
use App\Jobs\Webinars\ProcessPostWebinarEventJob;

class HandleWebinarProviderWebhookEventAction
{
    public function execute(ProviderWebhookEvent $event): void
    {
        if (! $event->isWebinarEnded()) {
            return;
        }

        if (! $event->externalWebinarId) {
            return;
        }

        ProcessPostWebinarEventJob::dispatch(
            provider: $event->provider,
            externalWebinarId: $event->externalWebinarId,
        )->onQueue('webinars');
    }
}