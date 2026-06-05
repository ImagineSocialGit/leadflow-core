<?php

namespace App\Contracts\Messaging\Email;

use Illuminate\Mail\Mailable;

interface EmailMessage
{
    public static function fromArray(array $payload): self;

    public function to(): string;

    public function mailable(): Mailable;

    public function devPayload(): array;
}