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

trait ArrayAccessTrait
{
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
     * Whether a offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $element
     *
     * @return bool
     */
    public function offsetExists(mixed $element): bool
    {
        $element = (string)$element;

        return $this->has($element);
    }

    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $element
     *
     * @return mixed
     */
    public function offsetGet(mixed $element): mixed
    {
        $element = (string)$element;

        return $this->get($element);
    }

    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $element
     * @param mixed $value
     */
    public function offsetSet(mixed $element, mixed $value): void
    {
        $element = (string)$element;

        $this->set($element, $value);
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $element
     */
    public function offsetUnset(mixed $element): void
    {
        $element = (string)$element;

        $this->remove($element);
    }

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
    abstract public function set(string $element, mixed $value): void;
}
