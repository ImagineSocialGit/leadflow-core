<?php

namespace App\Services\Messaging;

use App\Enums\MessageChannel;
use InvalidArgumentException;

class MessageDefinitionResolver
{
    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    public function resolve(
        MessageChannel|string $channel,
        string $scope,
        string $message,
        ?string $variant = null,
        array $context = [],
    ): array {
        $channel = $channel instanceof MessageChannel
            ? $channel->value
            : strtolower(trim($channel));

        $scope = trim($scope);
        $configScope = str_replace('-', '_', $scope);

        if ($channel === '' || $scope === '' || trim($message) === '') {
            return [];
        }

        $baseConfigPath = "messaging.{$channel}.{$configScope}.{$message}";
        $definition = config($baseConfigPath);

        if (! is_array($definition)) {
            return [];
        }

        $overrideConfigPath = null;
        $webinarSlug = $context['webinar_slug'] ?? null;

        if (is_string($webinarSlug) && trim($webinarSlug) !== '') {
            $overrideConfigPath = "messaging.{$channel}.{$configScope}.overrides.{$webinarSlug}.{$message}";
            $override = config($overrideConfigPath);

            if (is_array($override)) {
                $definition = array_replace_recursive($definition, $override);
            }
        }

        if (! ($definition['enabled'] ?? true)) {
            return [];
        }

        $definition['scope'] = $definition['scope'] ?? $scope;
        $definition['config_path'] = $baseConfigPath;
        $definition['override_config_path'] = is_array(config($overrideConfigPath)) ? $overrideConfigPath : null;

        if (($definition['scope'] ?? null) !== $scope) {
            throw new InvalidArgumentException(
                "Message definition [{$baseConfigPath}] has scope [{$definition['scope']}], expected [{$scope}]."
            );
        }

        $variants = $definition['variants'] ?? null;

        if (! is_array($variants)) {
            return [$this->normalizeDefinition($definition, $message)];
        }

        if ($variant !== null) {
            $variantDefinition = $variants[$variant] ?? null;

            if (! is_array($variantDefinition) || ! ($variantDefinition['enabled'] ?? true)) {
                return [];
            }

            return [
                $this->normalizeDefinition(
                    array_merge(
                        $this->withoutVariants($definition),
                        $variantDefinition,
                        [
                            'variant' => $variant,
                            'message_type' => $variantDefinition['message_type'] ?? $variant,
                        ],
                    ),
                    $message,
                ),
            ];
        }

        $resolved = [];

        foreach ($variants as $variantKey => $variantDefinition) {
            if (! is_array($variantDefinition) || ! ($variantDefinition['enabled'] ?? true)) {
                continue;
            }

            $resolved[] = $this->normalizeDefinition(
                array_merge(
                    $this->withoutVariants($definition),
                    $variantDefinition,
                    [
                        'variant' => $variantKey,
                        'message_type' => $variantDefinition['message_type'] ?? $variantKey,
                    ],
                ),
                $message,
            );
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function normalizeDefinition(array $definition, string $message): array
    {
        $messageType = $definition['message_type'] ?? $message;

        if (! is_string($messageType) || trim($messageType) === '') {
            throw new InvalidArgumentException('Message definition is missing a valid message_type.');
        }

        foreach (['purpose', 'scope', 'payload_class'] as $requiredKey) {
            if (! is_string($definition[$requiredKey] ?? null) || trim($definition[$requiredKey]) === '') {
                throw new InvalidArgumentException("Message definition [{$messageType}] is missing [{$requiredKey}].");
            }
        }

        return array_merge($definition, [
            'message' => $message,
            'message_type' => $messageType,
        ]);
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function withoutVariants(array $definition): array
    {
        unset($definition['variants']);

        return $definition;
    }
}