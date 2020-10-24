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

namespace Phalcon\Config\Adapter;

use Phalcon\Config\Config;

use function file_get_contents;

/**
 * Reads JSON files and converts them to Phalcon\Config objects.
 *
 * Given the following configuration file:
 *
 *```json
 * {"phalcon":{"baseuri":"\/phalcon\/"},"models":{"metadata":"memory"}}
 *```
 *
 * You can read it as follows:
 *
 *```php
 * use Phalcon\Config\Adapter\Json;
 *
 * $config = new Json("path/config.json");
 *
 * echo $config->phalcon->baseuri;
 * echo $config->models->metadata;
 *```
 */
class Json extends Config
{
    /**
     * Json constructor.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        parent::__construct(
            json_decode(
                file_get_contents($filePath),
                true
            )
        );
    }
}
