<?php

namespace App\Http\Controllers;

use App\Actions\Webinars\GetActiveWebinarSeriesAction;
use App\Models\Lead;
use App\Models\WebinarWaitlistSignup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebinarWaitlistSignupController extends Controller
{
    public function __invoke(Request $request, string $seriesSlug): RedirectResponse
    {
        $series = app(GetActiveWebinarSeriesAction::class)
            ->findBySlug($seriesSlug);

        abort_unless($series, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'consent_messages' => ['accepted'],
        ]);

        $email = str($validated['email'])->lower()->trim()->toString();
        $phone = filled($validated['phone'] ?? null)
            ? preg_replace('/[^\d+]/', '', $validated['phone'])
            : null;

        Lead::query()->updateOrCreate(
            ['email' => $email],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'phone' => $phone,
                'source' => 'webinar_waitlist',
            ],
        );

        WebinarWaitlistSignup::query()->updateOrCreate(
            [
                'webinar_series_id' => $series->id,
                'email' => $email,
            ],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'phone' => $phone,
                'email_consent_at' => now(),
                'sms_consent_at' => $phone ? now() : null,
                'source_page' => route('webinar.show', $series->slug),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'meta' => [
                    'series_slug' => $series->slug,
                    'series_title' => $series->title,
                ],
            ],
        );

        return back()->with('success', 'You’re on the list. We’ll let you know when the next webinar is scheduled.');
    }
}