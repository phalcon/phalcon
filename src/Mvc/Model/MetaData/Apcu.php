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

namespace Phalcon\Mvc\Model\MetaData;

use Exception;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Mvc\Model\MetaData;

/**
 * Phalcon\Mvc\Model\MetaData\Apcu
 *
 * Stores model meta-data in the APCu cache. Data will erased if the web server is restarted
 *
 * By default meta-data is stored for 48 hours (172800 seconds)
 *
 * You can query the meta-data by printing apcu_fetch('$PMM$') or apcu_fetch('$PMM$my-app-id')
 *
 *```php
 * $metaData = new \Phalcon\Mvc\Model\MetaData\Apcu(
 *     [
 *         "prefix"   => "my-app-id",
 *         "lifetime" => 86400,
 *     ]
 * );
 *```
 */
class Apcu extends MetaData
{
    /**
     * Phalcon\Mvc\Model\MetaData\Apcu constructor
     *
     * @param AdapterFactory       $factory
     * @param array<string, mixed> $options
     *
     * @throws Exception
     */
    public function __construct(AdapterFactory $factory, array $options = [])
    {
        $options["prefix"]   = $options["prefix"] ?? "ph-mm-apcu-";
        $options["lifetime"] = $options["lifetime"] ?? 172800;
        $this->adapter       = $factory->newInstance("apcu", $options);
    }
}
