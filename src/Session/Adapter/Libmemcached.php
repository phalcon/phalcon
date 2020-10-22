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

use Phalcon\Storage\AdapterFactory;
use Phalcon\Support\Exception as SupportException;

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
     *     'servers'          => [
     *         [
     *             'host'   => 'localhost',
     *             'port'   => 11211,
     *             'weight' => 1,
     *
     *         ]
     *     ],
     *     'defaultSerializer' => 'php',
     *     'lifetime'          => 3600,
     *     'serializer'        => null,
     *     'prefix'            => 'sess-memc-'
     * ]
     *
     * @throws SupportException
     */
    public function __construct(AdapterFactory $factory, array $options = [])
    {
        $options["prefix"] = "sess-memc-";
        $this->adapter     = $factory->newInstance('libmemcached', $options);
    }
}
