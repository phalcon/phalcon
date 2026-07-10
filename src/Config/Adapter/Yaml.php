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
use Phalcon\Config\Exceptions\CannotLoadConfigFile;
use Phalcon\Config\Exceptions\MissingYamlExtension;
use Phalcon\Traits\Php\InfoTrait;
use Phalcon\Traits\Php\YamlTrait;

use function basename;

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
    use InfoTrait;
    use YamlTrait;

    /**
     * Yaml constructor.
     *
     * @param string                       $filePath
     * @param array<string, callable>|null $callbacks
     *
     * @throws Exception
     */
    public function __construct(string $filePath, array | null $callbacks = null)
    {
        $ndocs = 0;

        if (true !== $this->phpExtensionLoaded('yaml')) {
            throw new MissingYamlExtension();
        }

        $yamlConfig = $this->phpYamlParseFile(
            $filePath,
            0,
            $ndocs,
            $callbacks ?? []
        );

        if (null === $yamlConfig) {
            $yamlConfig = [];
        }

        if (false === $yamlConfig) {
            throw new CannotLoadConfigFile(basename($filePath));
        }

        /** @var array<string, callable> $yamlConfig */
        parent::__construct($yamlConfig);
    }
}
