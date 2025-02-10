<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures;

/**
 * Stores values in a static property. Useful for events testing
 */
class Storage
{
    /**
     * @var array<string, mixed>
     */
    private static array $data = [];

    /**
     * Return a stored element or `null` if it does not exist
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        return self::$data[$key] ?? null;
    }

    /**
     * Return the stored data
     *
     * @return array<string, mixed>
     */
    public static function getAll(): array
    {
        return self::$data;
    }

    /**
     * Return if an element exists in the store
     *
     * @param string $key
     *
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(self::$data[$key]);
    }

    /**
     * Remove a stored element
     *
     * @param string $key
     *
     * @return void
     */
    public static function remove(string $key): void
    {
        unset(self::$data[$key]);
    }

    /**
     * Reset the internal store
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$data = [];
    }

    /**
     * Set an element in the store
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }
}
