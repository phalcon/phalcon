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

use Exception;
use Phalcon\Session\Adapter\Exceptions\AdapterRuntimeError;
use Phalcon\Storage\AdapterFactory;

use function bin2hex;
use function random_bytes;
use function usleep;

/**
 * Phalcon\Session\Adapter\Redis
 */
class Redis extends AbstractAdapter
{
    /**
     * @var bool
     */
    protected bool $lockAcquired = false;

    /**
     * @var int
     */
    protected int $lockExpiry = 30;

    /**
     * @var bool
     */
    protected bool $lockingEnabled = false;

    /**
     * @var string
     */
    protected string $lockKey = '';

    /**
     * @var int
     */
    protected int $lockRetries = 100;

    /**
     * @var string
     */
    protected string $lockToken = '';

    /**
     * @var int
     */
    protected int $lockWaitTime = 50000;

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * Redis constructor.
     *
     * @param AdapterFactory $factory
     * @param array          $options = [
     *                                'prefix'         => 'sess-reds-',
     *                                'stripPrefix'    => false,
     *                                'host'           => '127.0.0.1',
     *                                'port'           => 6379,
     *                                'index'          => 0,
     *                                'persistent'     => false,
     *                                'auth'           => '',
     *                                'socket'         => '',
     *                                'lockingEnabled' => false,
     *                                'lockExpiry'     => 30,
     *                                'lockRetries'    => 100,
     *                                'lockWaitTime'   => 50000,
     * ]
     *
     * @throws Exception
     */
    public function __construct(AdapterFactory $factory, array $options = [])
    {
        /**
         * Session ids are externally generated and never carry the storage
         * prefix; disable prefix stripping so an id that happens to start
         * with the prefix text cannot collide with another session
         */
        $options['prefix']      = $options['prefix'] ?? 'sess-reds-';
        $options['stripPrefix'] = $options['stripPrefix'] ?? false;
        $this->lockExpiry       = (int) ($options['lockExpiry'] ?? 30);
        $this->lockingEnabled   = (bool) ($options['lockingEnabled'] ?? false);
        $this->lockRetries      = (int) ($options['lockRetries'] ?? 100);
        $this->lockWaitTime     = (int) ($options['lockWaitTime'] ?? 50000);
        $this->prefix           = (string) $options['prefix'];
        $this->adapter          = $factory->newInstance('redis', $options);
    }

    /**
     * Close - releases the session lock if one is held
     *
     * @return bool
     */
    public function close(): bool
    {
        $this->releaseLock();

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
        $result = parent::destroy($id);

        $this->releaseLock();

        return $result;
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
        if (true === $this->lockingEnabled && true !== $this->acquireLock($id)) {
            throw new AdapterRuntimeError(
                'Could not acquire the session lock with key: ' . $this->lockKey
            );
        }

        return parent::read($id);
    }

    /**
     * Tries to acquire the session lock, pausing `lockWaitTime` microseconds
     * between attempts, up to `lockRetries` times
     *
     * @param string $id
     *
     * @return bool
     */
    protected function acquireLock(string $id): bool
    {
        $this->lockKey = $this->prefix . $id . '-lock';
        $client        = $this->adapter->getAdapter();
        $token         = bin2hex(random_bytes(16));
        $attempt       = 0;

        while ($attempt < $this->lockRetries) {
            /**
             * rawCommand bypasses OPT_PREFIX and OPT_SERIALIZER so that the
             * lock value stays a plain string regardless of the configured
             * serializer; the key carries the session prefix manually
             */
            $result = $client->rawCommand(
                'SET',
                $this->lockKey,
                $token,
                'NX',
                'EX',
                $this->lockExpiry
            );

            if (false !== $result) {
                $this->lockAcquired = true;
                $this->lockToken    = $token;

                return true;
            }

            usleep($this->lockWaitTime);

            $attempt++;
        }

        return false;
    }

    /**
     * Releases the session lock - only when this instance still owns it
     *
     * @return void
     */
    protected function releaseLock(): void
    {
        if (true !== $this->lockAcquired) {
            return;
        }

        $script = "if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('del', KEYS[1]) else return 0 end";
        $client = $this->adapter->getAdapter();

        $client->rawCommand('EVAL', $script, 1, $this->lockKey, $this->lockToken);

        $this->lockAcquired = false;
        $this->lockToken    = '';
    }
}
