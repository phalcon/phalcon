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
use Phalcon\Config\Exception;

use function basename;
use function extension_loaded;
use function yaml_parse_file;

/**
 * Reads YAML files and converts them to Phalcon\Config objects.
 *
 * Given the following configuration file:
 *
 *```yaml
 * phalcon:
 *   baseuri:        /phalcon/
 *   controllersDir: !approot  /app/controllers/
 * models:
 *   metadata: memory
 *```
 *
 * You can read it as follows:
 *
 *```php
 * define(
 *     "APPROOT",
 *     dirname(__DIR__)
 * );
 *
 * use Phalcon\Config\Adapter\Yaml;
 *
 * $config = new Yaml(
 *     "path/config.yaml",
 *     [
 *         "!approot" => function($value) {
 *             return APPROOT . $value;
 *         },
 *     ]
 * );
 *
 * echo $config->phalcon->controllersDir;
 * echo $config->phalcon->baseuri;
 * echo $config->models->metadata;
 *```
 */
class Yaml extends Config
{
    /**
     * Yaml constructor.
     *
     * @param string     $filePath
     * @param array|null $callbacks
     *
     * @throws Exception
     */
    public function __construct(string $filePath, ?array $callbacks = null)
    {
        if (false === extension_loaded('yaml')) {
            throw new Exception(
                'Yaml extension is not loaded'
            );
        }

        if (true === empty($callbacks)) {
            $yamlConfig = yaml_parse_file(
                $filePath
            );
        } else {
            $yamlConfig = yaml_parse_file(
                $filePath,
                0,
                $ndocs = 0,
                $callbacks
            );
        }

        if (false === $yamlConfig) {
            throw new Exception(
                'Configuration file ' . basename($filePath) . ' can\'t be loaded'
            );
        }

        parent::__construct($yamlConfig);
    }
}
