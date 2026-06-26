<?php

namespace App\Modules\Tasks\Services\ContactShow;

use App\Modules\Core\Contracts\Contacts\ContactShowDataProvider;
use App\Modules\Core\Models\Contact;
use App\Modules\InternalNotifications\Models\TeamMember;
use App\Modules\Tasks\Models\Task;

class ContactTasksShowDataProvider implements ContactShowDataProvider
{
    /**
     * @return array<string, mixed>
     */
    public function dataFor(Contact $contact): array
    {
        $taskView = request('task_view') === 'archived' ? 'archived' : 'active';

        return [
            'taskView' => $taskView,

            'tasks' => Task::query()
                ->with('assignedTo')
                ->where('related_type', $contact->getMorphClass())
                ->where('related_id', $contact->id)
                ->unarchived()
                ->latest()
                ->get(),

            'archivedTasks' => Task::query()
                ->with('assignedTo')
                ->where('related_type', $contact->getMorphClass())
                ->where('related_id', $contact->id)
                ->archived()
                ->latest('archived_at')
                ->get(),

            'teamMembers' => TeamMember::active()
                ->orderBy('name')
                ->get(['id', 'name', 'email']),

            'currentTeamMember' => TeamMember::query()
                ->where('user_id', auth()->id())
                ->first(),
        ];
    }
}