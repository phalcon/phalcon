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

namespace Phalcon\Config;

use Phalcon\Config\Adapter\Grouped;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Config\Adapter\Json;
use Phalcon\Config\Adapter\Php;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\Traits\FactoryTrait;

use function is_array;
use function is_object;
use function is_string;
use function lcfirst;
use function pathinfo;
use function strtolower;

/**
 * Loads Config Adapter class using 'adapter' option, if no extension is
 * provided it will be added to filePath
 *
 *```php
 * use Phalcon\Config\ConfigFactory;
 *
 * $options = [
 *     "filePath" => "path/config",
 *     "adapter"  => "php",
 * ];
 *
 * $config = (new ConfigFactory())->load($options);
 *```
 */
class ConfigFactory
{
    use FactoryTrait;

    /**
     * ConfigFactory constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Load a config to create a new instance
     *
     * @param string|array|Config $config  = [
     *                                     'adapter' => 'ini',
     *                                     'filePath' => 'config.ini',
     *                                     'mode' => null,
     *                                     'callbacks' => null
     *                                     ]
     *
     * @return ConfigInterface
     * @throws Exception
     * @throws SupportException
     */
    public function load($config): ConfigInterface
    {
        if (true === is_string($config)) {
            $oldConfig = $config;
            $extension = pathinfo($config, PATHINFO_EXTENSION);

            if (empty($extension)) {
                throw new Exception(
                    'You need to provide the extension in the file path'
                );
            }

            $config = [
                'adapter'  => $extension,
                'filePath' => $oldConfig
            ];
        }

        if (is_object($config) && $config instanceof ConfigInterface) {
            $config = $config->toArray();
        }

        if (false === is_array($config)) {
            throw new Exception(
                'Config must be array or Phalcon\\Config\\Config object'
            );
        }

        if (false === isset($config['filePath'])) {
            throw new Exception(
                'You must provide \'filePath\' option in factory config parameter.'
            );
        }

        if (false === isset($config['adapter'])) {
            throw new Exception(
                'You must provide \'adapter\' option in factory config parameter.'
            );
        }

        $adapter = strtolower($config['adapter']);
        $first   = $config['filePath'];
        $second  = null;

        if (empty(pathinfo($first, PATHINFO_EXTENSION))) {
            $first .= '.' . lcfirst($adapter);
        }

        if ('ini' === $adapter) {
            $second = $config['mode'] ?? 1;
        } elseif ('yaml' === $adapter) {
            $second = $config['callbacks'] ?? [];
        }

        return $this->newInstance($adapter, $first, $second);
    }

    /**
     * Returns a new Config instance
     *
     * @param string     $name
     * @param string     $fileName
     * @param mixed|null $params
     *
     * @return ConfigInterface
     * @throws SupportException
     */
    public function newInstance(string $name, string $fileName, $params = null): ConfigInterface
    {
        $definition = $this->getService($name);

        switch ($definition) {
            case Json::class:
            case Php::class:
                return new $definition($fileName);
        }

        return new $definition($fileName, $params);
    }

    /**
     * @return array
     */
    protected function getServices(): array
    {
        return [
            'grouped' => Grouped::class,
            'ini'     => Ini::class,
            'json'    => Json::class,
            'php'     => Php::class,
            'yaml'    => Yaml::class
        ];
    }
}
