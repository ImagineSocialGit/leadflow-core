<?php

namespace App\Contracts\Messaging\Sms;

interface SmsMessagePayload
{
    public static function fromArray(array $payload): self;

    public function to(): string;

    public function message(): string;

    public function kind(): string;

    public function devPayload(): array;

    public function sourceIp(): ?string;
}