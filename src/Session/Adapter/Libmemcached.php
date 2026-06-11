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
 * Class Libmemcached
 *
 * @package Phalcon\Session\Adapter
 */
class Libmemcached extends AbstractAdapter
{
    /**
     * Libmemcached constructor.
     *
     * @param AdapterFactory $factory
     * @param array          $options = [
     *                                'servers'          => [
     *                                [
     *                                'host'   => 'localhost',
     *                                'port'   => 11211,
     *                                'weight' => 1,
     *
     *         ]
     *     ],
     *     'defaultSerializer' => 'php',
     *     'lifetime'          => 3600,
     *     'serializer'        => null,
     *     'prefix'            => 'sess-memc-'
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
        $options['prefix']      = $options['prefix'] ?? 'sess-memc-';
        $options['stripPrefix'] = $options['stripPrefix'] ?? false;
        $this->adapter          = $factory->newInstance('libmemcached', $options);
    }
}
