<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\WebinarRegistration;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function index()
    {

        $contacts = Contact::latest()->paginate(20);

        return view('crm.contacts.index', compact('contacts'));
    }

    public function show(Contact $contact)
    {
        $contact->load([
            'registrations.webinar',
            'notes' => fn ($query) => $query->latest(),
            'tasks' => fn ($query) => $query->latest(),
        ]);

        return view('crm.contacts.show', compact('contact'));
    }

    public function markConverted(Contact $contact, WebinarRegistration $registration): RedirectResponse
    {
        // safety: ensure registration belongs to this contact
        if ($registration->contact_id !== $contact->id) {
            abort(404);
        }

        $registration->update([
            'converted_at' => now(),
        ]);

        return back();
    }
}
