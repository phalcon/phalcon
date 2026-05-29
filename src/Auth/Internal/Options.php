<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth\Internal;

use Phalcon\Auth\Exception;
use Phalcon\Contracts\Container\Service\Collection;

/**
 * Internal option-parsing helpers shared by adapter / guard fromOptions()
 * implementations. Not part of the public API.
 */
final class Options
{
    /**
     * @param array<string, mixed>                              $options
     * @param list<array{id?: int|string}&array<string, mixed>> $default
     *
     * @return list<array{id?: int|string}&array<string, mixed>>
     */
    public static function arrayOption(array $options, string $key, array $default): array
    {
        $value = $options[$key] ?? $default;

        if (!is_array($value)) {
            return $default;
        }

        /** @var list<array{id?: int|string}&array<string, mixed>> */
        return array_values($value);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws Exception
     */
    public static function requireString(array $options, string $key, string $context): string
    {
        $value = $options[$key] ?? null;

        if (!is_string($value) || $value === '') {
            throw new Exception(
                sprintf("Auth %s requires '%s' to be a non-empty string", $context, $key)
            );
        }

        return $value;
    }

    /**
     * @template T of object
     *
     * @phpstan-param class-string<T> $serviceId
     *
     * @phpstan-return T
     *
     * @throws Exception
     */
    public static function resolveService(
        Collection $container,
        string $serviceId,
        string $context
    ): object {
        if (!$container->has($serviceId)) {
            throw new Exception(
                sprintf(
                    "Auth %s requires service '%s' to be bound in the container",
                    $context,
                    $serviceId
                )
            );
        }

        /** @var T */
        return $container->get($serviceId);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function stringOrNull(array $options, string $key): ?string
    {
        $value = $options[$key] ?? null;

        return is_string($value) ? $value : null;
    }
}
