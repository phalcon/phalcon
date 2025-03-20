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

namespace Phalcon\Support\Collection\Traits;

trait GetSetHasTrait
{
    /**
     * Magic getter to get an element from the collection
     *
     * @param string $element
     *
     * @return mixed|null
     */
    public function __get(string $element): mixed
    {
        return $this->get($element);
    }

    /**
     * Magic isset to check whether an element exists or not
     *
     * @param string $element
     *
     * @return bool
     */
    public function __isset(string $element): bool
    {
        return $this->has($element);
    }

    /**
     * Magic setter to assign values to an element
     *
     * @param string $element
     * @param mixed  $value
     */
    public function __set(string $element, mixed $value): void
    {
        $this->set($element, $value);
    }

    /**
     * Magic unset to remove an element from the collection
     *
     * @param string $element
     */
    public function __unset(string $element): void
    {
        $this->remove($element);
    }

    /**
     * Get the element from the collection
     *
     * @param string      $element
     * @param mixed|null  $defaultValue
     * @param string|null $cast
     *
     * @return mixed
     */
    abstract public function get(
        string $element,
        mixed $defaultValue = null,
        string | null $cast = null
    ): mixed;

    /**
     * Get the element from the collection
     *
     * @param string $element
     *
     * @return bool
     */
    abstract public function has(string $element): bool;

    /**
     * Delete the element from the collection
     *
     * @param string $element
     */
    abstract public function remove(string $element): void;

    /**
     * Set an element in the collection
     *
     * @param string $element
     * @param mixed  $value
     */
    abstract public function set(string $element, $value): void;
}
