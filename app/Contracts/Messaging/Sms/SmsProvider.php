<?php

namespace App\Contracts\Messaging\Sms;

interface SmsProvider
{
    public function provider(): string;

    public function send(
        string $to,
        string $message,
        array $meta = [],
    ): void;
}