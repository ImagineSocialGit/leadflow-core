<x-layouts.crm
    :title="'Map CSV Fields'"
    :heading="'Map CSV Fields'"
    :subheading="'Choose which CSV columns map to contact fields'"
>
    <div class="max-w-4xl space-y-6">
        <x-ui.card class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold tracking-tight">
                    Contact Fields
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Select the CSV column for each CRM field.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('crm.contacts.import.process') }}"
            >
                @csrf
                @foreach ($headers as $header)
                    <input
                        type="hidden"
                        name="headers[]"
                        value="{{ $header }}"
                    >
                @endforeach

                <input
                    type="hidden"
                    name="csv_path"
                    value="{{ $csvPath }}"
                >

                @php
                    $fields = [
                        'first_name' => 'First Name',
                        'last_name' => 'Last Name',
                        'name' => 'Full Name',
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'closed_at' => 'Closed At',
                        'last_contacted_at' => 'Last Contacted At',
                        'loan_amount' => 'Loan Amount',
                        'rate' => 'Rate',
                        'title' => 'Title',
                        'mortgage_type' => 'Mortgage Type',
                        'loan_purpose' => 'Loan Purpose',
                        'loan_program' => 'Loan Program',
                        'lien_position' => 'Lien Position',
                        'stage_name' => 'Stage Name',
                    ];
                @endphp

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($fields as $field => $label)
                        <div>
                            <x-ui.form.label for="mapping_{{ $field }}">
                                {{ $label }}
                            </x-ui.form.label>

                            <select
                                id="mapping_{{ $field }}"
                                name="mapping[{{ $field }}]"
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Do not import</option>

                                @foreach ($headers as $header)
                                    <option value="{{ $header }}">
                                        {{ $header }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center gap-3 border-t border-slate-200 pt-6">
                    <x-ui.button type="submit">
                        Import Contacts
                    </x-ui.button>

                    <a
                        href="{{ route('crm.contacts.import') }}"
                        class="text-sm font-semibold text-slate-600 hover:underline"
                    >
                        Upload a different CSV
                    </a>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.crm>