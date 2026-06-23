<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\TeamMember;
use App\Models\WebinarRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::latest()->paginate(20);

        return view('crm.contacts.index', compact('contacts'));
    }

    public function show(Contact $contact)
    {
        $scheduledMessages = $contact->scheduledMessages()
            ->where('status', 'sent')
            ->latest('send_at')
            ->paginate(10, ['*'], 'messages_page')
            ->withQueryString();

        $contact->load([
            'registrations.webinar',
            'notes' => fn ($query) => $query->latest(),
            'tasks' => fn ($query) => $query
                ->with('assignedTo')
                ->latest(),
            'messageConsents',
            'consentRevocations',
        ]);

        $teamMembers = TeamMember::active()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $currentTeamMember = TeamMember::query()
            ->where('user_id', auth()->id())
            ->first();

        return view('crm.contacts.show', compact(
            'contact',
            'scheduledMessages',
            'teamMembers',
            'currentTeamMember',
        ));
    }

    public function markConverted(Contact $contact, WebinarRegistration $registration): RedirectResponse
    {
        if ($registration->contact_id !== $contact->id) {
            abort(404);
        }

        $contact->update([
            'converted_at' => now(),
        ]);

        return back();
    }

    public function import(): View
    {
        return view('crm.contacts.import');
    }

    public function previewImport(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $storedPath = $validated['csv']->store('imports', 'local');

        $handle = fopen(Storage::disk('local')->path($storedPath), 'r');

        if ($handle === false) {
            return back()
                ->withErrors(['csv' => 'Unable to read the uploaded CSV file.'])
                ->withInput();
        }

        $headers = fgetcsv($handle);

        if (! is_array($headers) || $headers === []) {
            fclose($handle);

            return back()
                ->withErrors(['csv' => 'The uploaded CSV does not contain a valid header row.'])
                ->withInput();
        }

        $headers = collect($headers)
            ->map(fn ($header) => trim((string) $header))
            ->filter()
            ->values();

        if ($headers->isEmpty()) {
            fclose($handle);

            return back()
                ->withErrors(['csv' => 'The uploaded CSV header row is empty.'])
                ->withInput();
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false && count($rows) < 20) {
            $row = array_pad($row, $headers->count(), null);

            $rows[] = array_combine(
                $headers->all(),
                array_slice($row, 0, $headers->count()),
            );
        }

        fclose($handle);

        return view('crm.contacts.import-preview', [
            'headers' => $headers,
            'rows' => $rows,
            'csvPath' => $storedPath,
        ]);
    }

    public function processImport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'csv_path' => ['required', 'string'],
            'mapping' => ['required', 'array'],
            'mapping.email' => ['required', 'string'],
        ]);

        $csvPath = $validated['csv_path'];
        $mapping = collect($validated['mapping'])->filter()->toArray();

        if (! Storage::disk('local')->exists($csvPath)) {
            throw ValidationException::withMessages([
                'csv_path' => 'The uploaded CSV file could not be found.',
            ]);
        }

        $handle = fopen(Storage::disk('local')->path($csvPath), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'csv_path' => 'The uploaded CSV file could not be opened.',
            ]);
        }

        $headers = fgetcsv($handle);

        if (! is_array($headers) || $headers === []) {
            fclose($handle);

            throw ValidationException::withMessages([
                'csv_path' => 'The uploaded CSV does not contain a valid header row.',
            ]);
        }

        $headers = collect($headers)
            ->map(fn ($header) => trim((string) $header))
            ->filter()
            ->values()
            ->all();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $phoneWarnings = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_pad($row, count($headers), null);

            $data = array_combine(
                $headers,
                array_slice($row, 0, count($headers)),
            );

            if (! is_array($data)) {
                $skipped++;
                continue;
            }

            $email = $this->mappedValue($data, $mapping, 'email');

            if ($email === null) {
                $skipped++;
                continue;
            }

            $email = strtolower($email);

            $phone = $this->mappedValue($data, $mapping, 'phone');

            if ($phone !== null) {
                $phoneWarnings += Contact::query()
                    ->where('phone', $phone)
                    ->where('email', '!=', $email)
                    ->exists()
                        ? 1
                        : 0;
            }

            $existing = Contact::query()
                ->where('email', $email)
                ->first();

            $firstName = $this->mappedValue($data, $mapping, 'first_name');
            $lastName = $this->mappedValue($data, $mapping, 'last_name');
            $name = $this->mappedValue($data, $mapping, 'name');

            if ($name === null) {
                $name = trim(collect([$firstName, $lastName])->filter()->implode(' '));
            }

            if ($name === '') {
                $name = $email;
            }

            $closedAt = $this->mappedDate($data, $mapping, 'closed_at');
            $lastContactedAt = $this->mappedDate($data, $mapping, 'last_contacted_at') ?? $closedAt;

            Contact::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $name,
                    'phone' => $phone,
                    'source' => 'import',
                    'subsource' => 'csv',
                    'crm_status' => $closedAt ? 'converted' : 'new',
                    'converted_at' => $closedAt,
                    'closed_at' => $closedAt,
                    'last_contacted_at' => $lastContactedAt,
                    'last_activity_at' => $lastContactedAt,
                ],
            );

            $existing ? $updated++ : $created++;
        }

        fclose($handle);

        Storage::disk('local')->delete($csvPath);

        return redirect()
            ->route('crm.contacts.index')
            ->with(
                'success',
                "{$created} contacts created. {$updated} contacts updated. {$skipped} rows skipped. {$phoneWarnings} phone duplicate warnings."
            );
    }

    private function mappedValue(array $row, array $mapping, string $field): ?string
    {
        $column = $mapping[$field] ?? null;

        if ($column === null || ! array_key_exists($column, $row)) {
            return null;
        }

        $value = trim((string) $row[$column]);

        return $value !== '' ? $value : null;
    }

    private function mappedDate(array $row, array $mapping, string $field): ?string
    {
        $value = $this->mappedValue($row, $mapping, $field);

        if ($value === null) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->startOfDay()->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}