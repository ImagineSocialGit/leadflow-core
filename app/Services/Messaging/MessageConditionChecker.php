<?php

namespace App\Services\Messaging;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class MessageConditionChecker
{
    /**
     * @param  array<string, mixed>  $conditions
     * @param  array<string, mixed>  $context
     */
    public function passes(array $conditions, array $context): bool
    {
        foreach ($conditions as $key => $expected) {
            if (! is_string($key) || trim($key) === '') {
                throw new InvalidArgumentException('Message condition keys must be non-empty strings.');
            }

            if (! $this->passesCondition(trim($key), $expected, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function passesCondition(string $key, mixed $expected, array $context): bool
    {
        [$path, $operator] = $this->parseKey($key);

        $actual = Arr::get($context, $path);

        return match ($operator) {
            'eq' => $actual == $expected,
            'not' => $actual != $expected,

            'in' => $this->expectedArrayContains($expected, $actual),
            'not_in' => ! $this->expectedArrayContains($expected, $actual),

            'exists' => Arr::has($context, $path),
            'missing' => ! Arr::has($context, $path),

            'truthy' => (bool) $actual === true,
            'falsy' => (bool) $actual === false,

            'gt' => is_numeric($actual) && is_numeric($expected) && $actual > $expected,
            'gte' => is_numeric($actual) && is_numeric($expected) && $actual >= $expected,
            'lt' => is_numeric($actual) && is_numeric($expected) && $actual < $expected,
            'lte' => is_numeric($actual) && is_numeric($expected) && $actual <= $expected,

            default => throw new InvalidArgumentException("Unsupported message condition operator [{$operator}]."),
        };
    }

    /**
     * Supports:
     *
     * contact.status => 'converted'
     * contact.status_not_in => ['converted']
     * webinar_registration.attended => false
     * contact.email_exists => true
     *
     * @return array{0: string, 1: string}
     */
    private function parseKey(string $key): array
    {
        $operators = [
            '_not_in' => 'not_in',
            '_exists' => 'exists',
            '_missing' => 'missing',
            '_truthy' => 'truthy',
            '_falsy' => 'falsy',
            '_not' => 'not',
            '_gte' => 'gte',
            '_lte' => 'lte',
            '_gt' => 'gt',
            '_lt' => 'lt',
            '_in' => 'in',
        ];

        foreach ($operators as $suffix => $operator) {
            if (str_ends_with($key, $suffix)) {
                return [
                    substr($key, 0, -strlen($suffix)),
                    $operator,
                ];
            }
        }

        return [$key, 'eq'];
    }

    private function expectedArrayContains(mixed $expected, mixed $actual): bool
    {
        if (! is_array($expected)) {
            throw new InvalidArgumentException('Message condition [in/not_in] operators require an array value.');
        }

        return in_array($actual, $expected, true);
    }
}