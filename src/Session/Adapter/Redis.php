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
use Phalcon\Storage\AdapterFactory;

/**
 * Phalcon\Session\Adapter\Redis
 */
class Redis extends AbstractAdapter
{
    /**
     * Redis constructor.
     *
     * @param AdapterFactory $factory
     * @param array          $options = [
     *                                'prefix'     => 'sess-reds-',
     *                                'host'       => '127.0.0.1',
     *                                'port'       => 6379,
     *                                'index'      => 0,
     *                                'persistent' => false,
     *                                'auth'       => '',
     *                                'socket'     => '',
     *
     * @throws Exception
     */
    public function __construct(AdapterFactory $factory, array $options = [])
    {
        $options['prefix'] = $options['prefix'] ?? 'sess-reds-';
        $this->adapter     = $factory->newInstance('redis', $options);
    }
}
