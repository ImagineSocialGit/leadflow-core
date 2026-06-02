<?php

namespace App\Data;

use App\Models\Contact;
use App\Models\WebinarRegistration;
use App\Support\Webinars\WebinarJoinLinkGenerator;
use Carbon\Carbon;
use Carbon\CarbonInterface;

readonly class WebinarMessageData
{
    public function __construct(
        public int $registrationId,
        public int $contactId,
        public string $contactFirstName,
        public ?string $contactLastName,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public int $webinarId,
        public string $webinarSlug,
        public string $webinarTitle,
        public CarbonInterface $webinarStartsAt,
        public string $webinarTimezone,
        public string $webinarJoinUrl,
        public string $webinarPlatform,
        public ?string $requestIp = null,
    ) {}

    public static function fromRegistration(WebinarRegistration $registration): self
    {
        $registration->loadMissing(['contact', 'webinar']);

        $contact = $registration->contact;
        $webinar = $registration->webinar;
        $joinLinkGenerator = app(WebinarJoinLinkGenerator::class);

        return new self(
            registrationId: $registration->id,
            contactId: $contact->id,
            contactFirstName: $contact->first_name ?? 'there',
            contactLastName: $contact->last_name,
            contactEmail: $contact->email,
            contactPhone: $contact->phone,
            webinarId: $webinar->id,
            webinarSlug: $webinar->slug,
            webinarTitle: $webinar->title,
            webinarStartsAt: $webinar->starts_at,
            webinarTimezone: $webinar->timezone ?: config('app.timezone', 'America/Chicago'),
            webinarJoinUrl: $joinLinkGenerator->forRegistration($registration),
            webinarPlatform: $webinar->platform,
            requestIp: $registration->meta['request_ip']
                ?? $registration->meta['ip_address']
                ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'registration_id' => $this->registrationId,
            'contact_id' => $this->contactId,
            'contact_first_name' => $this->contactFirstName,
            'contact_last_name' => $this->contactLastName,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'webinar_id' => $this->webinarId,
            'webinar_slug' => $this->webinarSlug,
            'webinar_title' => $this->webinarTitle,
            'webinar_starts_at' => $this->webinarStartsAt->toIso8601String(),
            'webinar_timezone' => $this->webinarTimezone,
            'webinar_join_url' => $this->webinarJoinUrl,
            'webinar_platform' => $this->webinarPlatform,
            'request_ip' => $this->requestIp,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            registrationId: $data['registration_id'],
            contactId: $data['contact_id'],
            contactFirstName: $data['contact_first_name'],
            contactLastName: $data['contact_last_name'] ?? null,
            contactEmail: $data['contact_email'],
            contactPhone: $data['contact_phone'],
            webinarId: $data['webinar_id'],
            webinarSlug: $data['webinar_slug'],
            webinarTitle: $data['webinar_title'],
            webinarStartsAt: Carbon::parse($data['webinar_starts_at']),
            webinarTimezone: $data['webinar_timezone'],
            webinarJoinUrl: $data['webinar_join_url'],
            webinarPlatform: $data['webinar_platform'],
            requestIp: $data['request_ip'] ?? null,
        );
    }

    public function formattedStart(string $format = 'M j g:i A'): string
    {
        return $this->webinarStartsAt
            ->copy()
            ->setTimezone($this->webinarTimezone)
            ->format($format);
    }

    public function contact(): Contact
    {
        return Contact::query()->findOrFail($this->contactId);
    }
}
