<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreContactTaskRequest;
use App\Models\Contact;
use App\Models\Task;

class ContactTaskController extends Controller
{
    public function store(StoreContactTaskRequest $request, Contact $contact)
    {
        $contact->tasks()->create([
            'title' => $request->validated()['title'],
            'due_at' => $request->validated()['due_at'] ?? null,
            'status' => 'open',
        ]);

        return redirect()->back();
    }

    public function complete(Contact $contact, Task $task)
    {
        abort_unless($task->contact_id === $contact->id, 404);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $contact->update([
            'last_contacted_at' => now(),
        ]);

        return redirect()->back();
    }

    public function reopen(Contact $contact, Task $task)
    {
        abort_unless($task->contact_id === $contact->id, 404);

        $task->update([
            'status' => 'open',
            'completed_at' => null,
        ]);

        return redirect()->back();
    }
}
