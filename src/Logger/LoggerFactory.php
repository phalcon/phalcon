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

namespace Phalcon\Logger;

use DateTimeZone;
use Exception as BaseException;
use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Logger\LoggerTypes;
use Phalcon\Factory\AbstractConfigFactory;

/**
 * Factory creating logger objects
 *
 * @phpstan-import-type logger_adapter_config from LoggerTypes
 * @phpstan-import-type logger_adapters from LoggerTypes
 * @phpstan-import-type logger_factory_config from LoggerTypes
 */
class LoggerFactory extends AbstractConfigFactory
{
    private AdapterFactory $adapterFactory;

    /**
     * Constructor
     */
    public function __construct(AdapterFactory $factory)
    {
        $this->adapterFactory = $factory;
    }

    /**
     * Factory to create an instance from a Config object
     *
     * The adapter list lives under `options`, not at the top level.
     *
     * @phpstan-param ConfigInterface|logger_factory_config $config
     *
     * @param array|ConfigInterface $config = [
     *     'name'    => 'messages',
     *     'options' => [
     *         'adapters' => [
     *             'adapter-name' => [
     *                  'adapter' => 'stream',
     *                  'name'    => 'file.log',
     *                  'options' => [
     *                      'mode'     => 'ab',
     *                      'option'   => null,
     *                      'facility' => null
     *                  ],
     *             ],
     *         ],
     *     ]
     * ]
     */
    public function load(mixed $config): Logger
    {
        $data   = [];
        $config = $this->checkConfig($config);

        /** @phpstan-var logger_factory_config $config */
        $config = $this->checkConfigElement($config, "name");

        $name     = $config["name"];
        $timezone = $config["timezone"] ?? null;
        $options  = $config["options"] ?? [];
        $adapters = $options["adapters"] ?? [];

        foreach ($adapters as $adapterName => $adapter) {
            $adapterClass    = $adapter["adapter"];
            $adapterFileName = $adapter["name"];
            $adapterOptions  = $adapter["options"] ?? [];

            $data[$adapterName] = $this->adapterFactory->newInstance(
                $adapterClass,
                $adapterFileName,
                $adapterOptions
            );
        }

        return $this->newInstance($name, $data, $timezone);
    }

    /**
     * Returns a Logger object
     *
     * @phpstan-param logger_adapters $adapters
     *
     * @throws BaseException
     */
    public function newInstance(
        string $name,
        array $adapters = [],
        DateTimeZone | null $timezone = null
    ): Logger {
        return new Logger($name, $adapters, $timezone);
    }

    /**
     * @return class-string<\Exception>
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }
}
