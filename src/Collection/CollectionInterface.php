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

namespace Phalcon\Collection;

/**
 * Phalcon\Collection\CollectionInterface
 *
 * Interface for Phalcon\Collection class
 */
interface CollectionInterface
{
    public function __get(string $element);

    public function __isset(string $element): bool;

    public function __set(string $element, $value): void;

    public function __unset(string $element): void;

    /**
     * Clears the internal collection
     */
    public function clear(): void;

    /**
     * Get the element from the collection
     *
     * @param string      $element
     * @param mixed|null  $defaultValue
     * @param string|null $cast
     *
     * @return mixed
     */
    public function get(
        string $element,
        $defaultValue = null,
        string $cast = null
    );

    /**
     * Returns the keys (insensitive or not) of the collection
     *
     * @param bool $insensitive Case insensitive keys (default: true)
     *
     * @return array
     */
    public function getKeys(bool $insensitive = true): array;

    /**
     * Returns the values of the internal array
     *
     * @return array
     */
    public function getValues(): array;

    /**
     * Get the element from the collection
     *
     * @param string $element Name of the element
     *
     * @return bool
     */
    public function has(string $element): bool;

    /**
     * Initialize internal array
     *
     * @param array $data Array to initialize the collection with
     */
    public function init(array $data = []): void;

    /**
     * Delete the element from the collection
     *
     * @param string $element Name of the element
     */
    public function remove(string $element): void;

    /**
     * Set an element in the collection
     *
     * @param string $element Name of the element
     * @param mixed  $value   Value to store for the element
     */
    public function set(string $element, $value): void;

    /**
     * Returns the object in an array format
     */
    public function toArray(): array;

    /**
     * Returns the object in a JSON format
     *
     * The following options are used if none specified for json_encode
     *
     * JSON_HEX_TAG, JSON_HEX_APOS, JSON_HEX_AMP, JSON_HEX_QUOT,
     * JSON_UNESCAPED_SLASHES, JSON_THROW_ON_ERROR
     *
     * @see https://www.ietf.org/rfc/rfc4627.txt
     *
     * @param int $options `
     *
     * @return string
     */
    public function toJson(int $options = 4194383): string;


}
