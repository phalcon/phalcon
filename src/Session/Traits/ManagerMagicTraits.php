<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Session\Traits;

/**
 * Trait ManagerMagicTraits
 *
 * @package Phalcon\Session\Traits
 */
trait ManagerMagicTraits
{
    /**
     * Alias: Gets a session variable from an application context
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Alias: Check whether a session variable is set in an application context
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Alias: Sets a session variable in an application context
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Alias: Removes a session variable from an application context
     *
     * @param string $key
     */
    public function __unset(string $key): void
    {
        $this->remove($key);
    }

    /**
     * Gets a session variable from an application context
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     * @param bool       $remove
     *
     * @return mixed|null
     */
    abstract public function get(
        string $key,
               $defaultValue = null,
        bool   $remove = false
    );

    /**
     * Check whether a session variable is set in an application context
     *
     * @param string $key
     *
     * @return bool
     */
    abstract public function has(string $key): bool;

    /**
     * Removes a session variable from an application context
     *
     * @param string $key
     */
    abstract public function remove(string $key): void;

    /**
     * Sets a session variable in an application context
     *
     * @param string $key
     * @param mixed  $value
     */
    abstract public function set(string $key, $value): void;
}
