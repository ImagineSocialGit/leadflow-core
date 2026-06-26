<?php

namespace App\Support\Modules;

use Illuminate\Support\Arr;

class ModuleManager
{
    /**
     * Explicitly enabled module keys plus core.
     *
     * @return array<string>
     */
    public function enabledKeys(): array
    {
        $enabled = config('modules.enabled', []);

        if (! is_array($enabled)) {
            return ['core'];
        }

        $keys = array_values(array_filter(
            array_map('strval', $enabled),
            fn (string $key): bool => $key !== ''
        ));

        return array_values(array_unique([
            'core',
            ...$keys,
        ]));
    }

    /**
     * Enabled module keys plus required dependency keys.
     *
     * @return array<string>
     */
    public function enabledKeysWithDependencies(): array
    {
        $resolved = [];

        foreach ($this->enabledKeys() as $key) {
            $this->addEnabledKeyWithDependencies($key, $resolved);
        }

        return array_values(array_unique($resolved));
    }

    public function enabled(string $key): bool
    {
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
        return array_values(array_filter(
            Arr::wrap(config("modules.modules.{$key}.depends_on", [])),
            fn (mixed $dependency): bool => is_string($dependency) && $dependency !== '',
        ));
    }

    public function known(string $key): bool
    {
        return array_key_exists($key, $this->definitions());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        $definitions = config('modules.modules', []);

        return is_array($definitions) ? $definitions : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function enabledDefinitions(): array
    {
        return array_intersect_key(
            $this->definitions(),
            array_flip($this->enabledKeys()),
        );
    }

    /**
     * @return array<class-string>
     */
    public function providers(string $key): array
    {
        return array_values(array_filter(
            Arr::wrap(config("modules.modules.{$key}.providers", [])),
            fn (mixed $provider): bool => is_string($provider) && $provider !== '',
        ));
    }

    /**
     * @return array<class-string>
     */
    public function enabledProviders(): array
    {
        $providers = [];

        foreach ($this->enabledKeysWithDependencies() as $key) {
            foreach ($this->providers($key) as $provider) {
                $providers[] = $provider;
            }
        }

        return array_values(array_unique($providers));
    }

    /**
     * @param  array<int, string>  $resolved
     * @param  array<int, string>  $resolving
     */
    private function addEnabledKeyWithDependencies(
        string $key,
        array &$resolved,
        array $resolving = [],
    ): void {
        if (in_array($key, $resolved, true)) {
            return;
        }

        if (in_array($key, $resolving, true)) {
            return;
        }

        if (! $this->known($key)) {
            $resolved[] = $key;

            return;
        }

        $resolving[] = $key;

        foreach ($this->dependencies($key) as $dependency) {
            $this->addEnabledKeyWithDependencies($dependency, $resolved, $resolving);
        }

        $resolved[] = $key;
    }
}