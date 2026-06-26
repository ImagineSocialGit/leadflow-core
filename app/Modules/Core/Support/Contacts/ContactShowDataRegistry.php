<?php

namespace App\Modules\Core\Support\Contacts;

use App\Modules\Core\Contracts\Contacts\ContactShowDataProvider;
use App\Modules\Core\Models\Contact;

class ContactShowDataRegistry
{
    /**
     * @param iterable<int, ContactShowDataProvider> $providers
     */
    public function __construct(
        private readonly iterable $providers,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dataFor(Contact $contact): array
    {
        $data = [];

        foreach ($this->providers as $provider) {
            $data = array_replace_recursive($data, $provider->dataFor($contact));
        }

        return $data;
    }
}