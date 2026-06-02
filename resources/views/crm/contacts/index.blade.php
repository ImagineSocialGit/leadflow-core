<x-layouts.crm
    :title="config('contacts.labels.plural')"
    :heading="config('contacts.labels.plural')"
    :subheading="config('contacts.labels.singular').' list'"
>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight capitalize">
                    All {{ config('contacts.labels.plural') }}
                </h2>
            </div>
        </div>

        <x-ui.card padding="none" class="overflow-hidden">
            <div class="divide-y divide-slate-200">
                @forelse ($contacts as $contact)
                    <a
                        href="{{ route('crm.contacts.show', $contact) }}"
                        class="block px-6 py-4 transition hover:bg-slate-50"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-900">
                                    {{ $contact->name }}
                                </p>

                                <p class="text-sm text-slate-500">
                                    {{ $contact->email }}
                                </p>
                            </div>

                            <div class="text-sm text-slate-500">
                                {{ ucfirst($contact->status) }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-8 text-sm text-slate-500">
                        No {{ strtolower(config('contacts.labels.plural')) }} yet.
                    </div>
                @endforelse
            </div>
        </x-ui.card>

        <div>
            {{ $contacts->links() }}
        </div>
    </div>
</x-layouts.crm>