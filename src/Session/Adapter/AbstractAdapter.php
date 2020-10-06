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

use Phalcon\Storage\Adapter\AdapterInterface;
use SessionHandlerInterface;

/**
 * Class AbstractAdapter
 *
 * @package Phalcon\Session\Adapter
 *
 * @property AdapterInterface $adapter
 */
abstract class AbstractAdapter implements SessionHandlerInterface
{
    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $adapter;

    /**
     * Close
     *
     * @return bool
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
        if (true !== empty($sessionId) && $this->adapter->has($sessionId)) {
            return $this->adapter->delete($sessionId);
        }

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
        $data = $this->adapter->get($sessionId);

        return null === $data ? '' : $data;
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
     *
     * @param string $sessionId
     * @param string $data
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        return $this->adapter->set($sessionId, $data);
    }
}
