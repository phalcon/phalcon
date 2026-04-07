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

namespace Phalcon\Mvc\Model\MetaData\Adapter;

use Exception;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Mvc\Model\MetaData;

/**
 * Stores model meta-data in the Memcache.
 *
 * By default meta-data is stored for 48 hours (172800 seconds)
 */
class Libmemcached extends MetaData
{
    /**
     * Phalcon\Mvc\Model\MetaData\Libmemcached constructor
     *
     * @param AdapterFactory       $factory
     * @param array<string, mixed> $options
     *
     * @throws Exception
     */
    public function __construct(AdapterFactory $factory, array $options = [])
    {
        $options["persistentId"] = $options["persistentId"] ?? "ph-mm-mcid-";
        $options["prefix"]       = $options["prefix"] ?? "ph-mm-memc-";
        $options["lifetime"]     = $options["lifetime"] ?? 172800;
        $this->adapter           = $factory->newInstance("libmemcached", $options);
    }

    /**
     * Flush Memcache data and resets internal meta-data in order to regenerate it
     */
    public function reset(): void
    {
        $this->adapter->clear();
        parent::reset();
    }
}
