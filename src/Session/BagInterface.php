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

use Phalcon\Contracts\Session\SessionTypes;

/**
 * Interface for Phalcon\Session\Bag
 *
 * @phpstan-import-type session_bag_data from SessionTypes
 */
interface BagInterface
{
    public function __get(string $element): mixed;

    public function __isset(string $element): bool;

    public function __set(string $element, mixed $value): void;

    public function __unset(string $element): void;

    public function clear(): void;

    public function get(
        string $element,
        mixed $defaultValue = null,
        string | null $cast = null
    ): mixed;

    public function has(string $element): bool;

    /**
     * @phpstan-param session_bag_data $data
     */
    public function init(array $data = []): void;

    public function remove(string $element): void;

    public function set(string $element, mixed $value): void;
}
