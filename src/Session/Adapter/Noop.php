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

namespace Phalcon\Session\Adapter;

use SessionHandlerInterface;

/**
 * Phalcon\Session\Adapter\Noop
 *
 * This is an "empty" or null adapter. It can be used for testing or any
 * other purpose that no session needs to be invoked
 *
 * ```php
 * <?php
 *
 * use Phalcon\Session\Manager;
 * use Phalcon\Session\Adapter\Noop;
 *
 * $session = new Manager();
 * $session->setAdapter(new Noop());
 * ```
 */
class Noop implements SessionHandlerInterface
{
    /**
     * Close
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroy
     *
     * @param string $id
     *
     * @return bool
     */
    public function destroy(string $id): bool
    {
        return true;
    }

    /**
     * Garbage Collector
     *
     * @param int $max_lifetime
     *
     * @return false|int
     */
    public function gc(int $max_lifetime): false | int
    {
        return 1;
    }

    /**
     * Open
     *
     * @param string $path
     * @param string $name
     *
     * @return bool
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Read
     *
     * @param string $id
     *
     * @return string
     */
    public function read(string $id): string
    {
        return '';
    }

    /**
     * Write
     *
     * @param string $id
     * @param string $data
     *
     * @return bool
     */
    public function write(string $id, string $data): bool
    {
        return true;
    }
}
