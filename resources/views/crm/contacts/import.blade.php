<x-layouts.crm
    :title="'Import '.config('contacts.labels.plural')"
    :heading="'Import '.config('contacts.labels.plural')"
    :subheading="'Upload a CSV file'"
>
    <div class="max-w-2xl space-y-6">
        <x-ui.card class="space-y-4">
            <form
                method="POST"
                action="{{ route('crm.contacts.import.preview') }}"
                enctype="multipart/form-data"
                class="space-y-4"
            >
                @csrf

                <div>
                    <x-ui.form.label for="csv">
                        CSV File
                    </x-ui.form.label>

                    <x-ui.form.input
                        id="csv"
                        name="csv"
                        type="file"
                        accept=".csv,text/csv"
                        required
                    />

                    @error('csv')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <x-ui.button type="submit">
                    Continue
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.crm>