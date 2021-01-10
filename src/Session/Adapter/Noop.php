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

namespace Phiz\Session\Adapter;

use SessionHandlerInterface;

/**
 * Phiz\Session\Adapter\Noop
 *
 * This is an "empty" or null adapter. It can be used for testing or any
 * other purpose that no session needs to be invoked
 *
 * ```php
 * <?php
 *
 * use Phiz\Session\Manager;
 * use Phiz\Session\Adapter\Noop;
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
     * @param string $sessionId
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        return true;
    }

    /**
     * Garbage Collector
     *
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }

    /**
     * Read
     *
     * @param string $sessionId
     *
     * @return string
     */
    public function read($sessionId): string
    {
        return '';
    }

    /**
     * Open
     *
     * @param string $savePath
     * @param string $sessionName
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * Write
     *
     * @param string $sessionId
     * @param string $data
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        return true;
    }
}
