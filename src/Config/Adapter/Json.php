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
use Phalcon\Config\Exceptions\CannotLoadConfigFile;
use Phalcon\Contracts\Config\ConfigTypes;
use Phalcon\Support\Helper\Json\Decode;
use Phalcon\Traits\Php\FileTrait;

use function basename;

/**
 * Reads JSON files and converts them to Phalcon\Config\Config objects.
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
 *
 * @phpstan-import-type config_data from ConfigTypes
 */
class Json extends Config
{
    use FileTrait;

    /**
     * Json constructor.
     *
     * @throws CannotLoadConfigFile
     */
    public function __construct(string $filePath)
    {
        $content = $this->phpFileGetContents($filePath);

        if (false === $content) {
            throw new CannotLoadConfigFile(basename($filePath));
        }

        /** @var config_data $data */
        $data = (new Decode())->__invoke($content, true);

        parent::__construct($data);
    }
}
