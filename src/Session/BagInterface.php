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

namespace Phalcon\Session;

use Phalcon\Di\InjectionAwareInterface;

/**
 * Interface for Phalcon\Session\Bag
 */
interface BagInterface extends InjectionAwareInterface
{
    /**
     * @param string $element
     *
     * @return mixed
     */
    public function __get(string $element): mixed;

    /**
     * @param string $element
     *
     * @return bool
     */
    public function __isset(string $element): bool;

    /**
     * @param string $element
     * @param mixed  $value
     *
     * @return void
     */
    public function __set(string $element, mixed $value): void;

    /**
     * @param string $element
     *
     * @return void
     */
    public function __unset(string $element): void;

    /**
     * @return void
     */
    public function clear(): void;

    /**
     * @param string      $element
     * @param mixed|null  $defaultValue
     * @param string|null $cast
     *
     * @return mixed
     */
    public function get(
        string $element,
        mixed $defaultValue = null,
        string | null $cast = null
    ): mixed;

    /**
     * @param string $element
     *
     * @return bool
     */
    public function has(string $element): bool;

    /**
     * @param array $data
     *
     * @return void
     */
    public function init(array $data = []): void;

    /**
     * @param string $element
     *
     * @return void
     */
    public function remove(string $element): void;

    /**
     * @param string $element
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $element, mixed $value): void;
}
