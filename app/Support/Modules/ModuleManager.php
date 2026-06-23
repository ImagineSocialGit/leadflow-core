<?php

namespace App\Support\Modules;

use Illuminate\Support\Arr;

class ModuleManager
{
    /**
     * @return array<string>
     */
    public function enabledKeys(): array
    {
        $enabled = config('modules.enabled', []);

        if (! is_array($enabled)) {
            return ['core'];
        }

        return array_values(array_unique([
            'core',
            ...array_map('strval', $enabled),
        ]));
    }

    public function enabled(string $key): bool
    {
        if ($key === 'core') {
            return true;
        }

        return in_array($key, $this->enabledKeys(), true);
    }

    public function disabled(string $key): bool
    {
        return ! $this->enabled($key);
    }

    public function require(string $key): void
    {
        abort_if($this->disabled($key), 404);
    }

    /**
     * @return array<string>
     */
    public function dependencies(string $key): array
    {
        return Arr::wrap(config("modules.modules.{$key}.depends_on", []));
    }

    public function known(string $key): bool
    {
        return array_key_exists($key, config('modules.modules', []));
    }
}