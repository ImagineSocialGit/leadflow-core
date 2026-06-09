<?php

use App\Http\Controllers\Webhooks\EmailWebhookController;
use App\Http\Controllers\Webhooks\SmsWebhookController;
use App\Http\Controllers\Webhooks\WebinarWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webinar/{provider}', WebinarWebhookController::class)
    ->whereIn('provider', array_keys(config('webinars.providers', [])))
    ->name('webhooks.webinar');

Route::post('/webhooks/zoom', WebinarWebhookController::class)
    ->defaults('provider', 'zoom')
    ->name('webhooks.zoom');

Route::post('/sms/{provider}', SmsWebhookController::class)
    ->whereIn('provider', ['twilio', 'telnyx'])
    ->name('webhooks.sms');

Route::post('/email/{provider}', EmailWebhookController::class)
    ->whereIn('provider', ['resend'])
    ->name('webhooks.email');