<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Mvc\Model\MetaData;

use Phiz\Helper\Arr;
use Phiz\Mvc\Model\Exception;
use Phiz\Mvc\Model\MetaData;
use Phiz\Cache\AdapterFactory;

/**
 * Phiz\Mvc\Model\MetaData\Libmemcached
 *
 * Stores model meta-data in the Memcache.
 *
 * By default meta-data is stored for 48 hours (172800 seconds)
 */
class Libmemcached extends MetaData
{
    /**
     * Phiz\Mvc\Model\MetaData\Libmemcached constructor
     *
     * @param array options
     */
    public function __construct(AdapterFactory $factory, array $options = [])
    {
            $options["persistentId"] = Arr::get($options, "persistentId", "ph-mm-mcid-");
            $options["prefix"]       = Arr::get($options, "prefix", "ph-mm-memc-");
            $options["lifetime"]     = Arr::get($options, "lifetime", 172800);
            $this->adapter           = $factory->newInstance("libmemcached", $options);
    }

    /**
     * Flush Memcache data and resets internal meta-data in order to regenerate it
     */
    public function reset() : void
    {
        $this->adapter->clear();

        parent::reset();
    }
}
