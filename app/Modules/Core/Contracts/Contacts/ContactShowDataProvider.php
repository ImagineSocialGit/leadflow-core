<?php

namespace App\Modules\Core\Contracts\Contacts;

use App\Modules\Core\Models\Contact;

interface ContactShowDataProvider
{
    /**
     * @return array<string, mixed>
     */
    public function dataFor(Contact $contact): array;
}