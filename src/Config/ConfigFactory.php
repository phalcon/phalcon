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

use Exception as BaseException;
use Phalcon\Config\Adapter\Grouped;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Config\Adapter\Json;
use Phalcon\Config\Adapter\Php;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Traits\Factory\FactoryTrait;

use function is_string;
use function lcfirst;
use function pathinfo;
use function strtolower;

use const INI_SCANNER_RAW;
use const PATHINFO_EXTENSION;

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
 *
 * @phpstan-type TConfig = array{
 *      adapter?: string,
 *      filePath?: string,
 *      mode?: string|null,
 *      callbacks?: array<string, callable>|null
 * }
 * @phpstan-type TConfigReturn = array{
 *      adapter: string,
 *      filePath: string,
 *      mode?: string|null,
 *      callbacks?: array<string, callable>|null
 * }
 */
class ConfigFactory
{
    use FactoryTrait;

    /**
     * ConfigFactory constructor.
     *
     * @param array<string, string> $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Load a config to create a new instance
     *
     * @param TConfig|string|Config $config
     *
     * @return ConfigInterface
     * @throws Exception
     */
    public function load(array | string | Config $config): ConfigInterface
    {
        $configArray = $this->parseConfig($config);

        $adapter  = strtolower($configArray['adapter']);
        $filePath = $configArray['filePath'];

        if (empty(pathinfo($filePath, PATHINFO_EXTENSION))) {
            $filePath .= '.' . lcfirst($adapter);
        }

        if ('ini' === $adapter) {
            return $this->newInstance(
                $adapter,
                $filePath,
                $configArray['mode'] ?? INI_SCANNER_RAW
            );
        } elseif ('yaml' === $adapter) {
            return $this->newInstance(
                $adapter,
                $filePath,
                $configArray['callbacks'] ?? null
            );
        }

        return $this->newInstance($adapter, $filePath);
    }

    /**
     * Returns a new Config instance
     *
     * @param string                                  $name
     * @param string                                  $fileName
     * @param array<string, callable>|string|int|null $params
     *
     * @return ConfigInterface
     * @throws BaseException
     */
    public function newInstance(
        string $name,
        string $fileName,
        array | string | int | null $params = null
    ): ConfigInterface {
        $definition = $this->getService($name);

        switch ($definition) {
            case Grouped::class:
                /** @var string|null $params */
                $adapter = null === $params ? 'php' : $params;
                /** @var Grouped $config */
                $config = new $definition($fileName, $adapter);
                break;
            case Ini::class:
                /** @var string|null $params */
                $mode = null === $params ? INI_SCANNER_RAW : $params;
                /** @var Ini $config */
                $config = new $definition($fileName, $mode);
                break;
            case Yaml::class:
                /** @var array<string, callable>|null $params */
                /** @var Yaml $config */
                $config = new $definition($fileName, $params);
                break;
            default:
                /** @var ConfigInterface $config */
                $config = new $definition($fileName);
                break;
        }

        return $config;
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            'grouped' => Grouped::class,
            'ini'     => Ini::class,
            'json'    => Json::class,
            'php'     => Php::class,
            'yaml'    => Yaml::class,
        ];
    }

    /**
     * @param TConfig|ConfigInterface|string $config
     *
     * @return TConfigReturn
     * @throws Exception
     */
    protected function parseConfig(array | ConfigInterface | string $config): array
    {
        if (is_string($config)) {
            $oldConfig = $config;
            $extension = pathinfo($config, PATHINFO_EXTENSION);

            if (empty($extension)) {
                throw new Exception(
                    'You need to provide the extension in the file path'
                );
            }

            $config = [
                'adapter'  => $extension,
                'filePath' => $oldConfig,
            ];
        }

        if ($config instanceof ConfigInterface) {
            $config = $config->toArray();
        }

        $this->checkConfigArray($config);

        return $config;
    }

    /**
     * @param TConfig $config
     *
     * @throws Exception
     */
    private function checkConfigArray(array $config): void
    {
        if (!isset($config['filePath'])) {
            throw new Exception(
                "You must provide 'filePath' option in factory config parameter."
            );
        }

        if (!isset($config['adapter'])) {
            throw new Exception(
                "You must provide 'adapter' option in factory config parameter."
            );
        }
    }
}
