<?php

use App\Http\Controllers\Webhooks\SmsWebhookController;
use App\Http\Controllers\Webhooks\ResendWebhookController;
use App\Http\Controllers\Webhooks\WebinarWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/zoom', WebinarWebhookController::class)
    ->name('webhooks.zoom');

Route::post('/sms/{provider}', SmsWebhookController::class)
    ->whereIn('provider', ['twilio', 'telnyx'])
    ->name('webhooks.sms');

Route::post('/webhooks/resend', ResendWebhookController::class)
    ->name('webhooks.resend');