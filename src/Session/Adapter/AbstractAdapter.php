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
     * @param string $id
     *
     * @return bool
     */
    public function destroy(string $id): bool
    {
        if (true !== empty($id) && $this->adapter->has($id)) {
            return $this->adapter->delete($id);
        }

        return true;
    }

    /**
     * Garbage Collector
     *
     * @param int $max_lifetime
     *
     * @return false|int
     */
    public function gc(int $max_lifetime): false|int
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
        $data = $this->adapter->get($id);

        return null === $data ? '' : $data;
    }

    /**
     * Write
     *
     *
     * @param string $id
     * @param string $data
     *
     * @return bool
     */
    public function write(string $id, string $data): bool
    {
        return $this->adapter->set($id, $data);
    }
}
