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
use Phalcon\Config\ConfigInterface;
use Phalcon\Support\Traits\ConfigTrait;

/**
 * Factory creating logger objects
 */
class LoggerFactory
{
    use ConfigTrait;

    /**
     * @var AdapterFactory
     */
    private AdapterFactory $adapterFactory;

    /**
     * @param AdapterFactory $factory
     */
    public function __construct(AdapterFactory $factory)
    {
        $this->adapterFactory = $factory;
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|ConfigInterface $config = [
     *                                      'name'     => 'messages',
     *                                      'adapters' => [
     *                                      'adapter-name' => [
     *                                      'adapter' => 'stream',
     *                                      'name'    => 'file.log',
     *                                      'options' => [
     *                                      'mode'     => 'ab',
     *                                      'option'   => null,
     *                                      'facility' => null
     *                                      ],
     *                                      ],
     *                                      ]
     *                                      ]
     */
    public function load($config): Logger
    {
        $data     = [];
        $config   = $this->checkConfig($config);
        $config   = $this->checkConfigElement($config, "name");
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
     * @param string            $name
     * @param array             $adapters
     * @param DateTimeZone|null $timezone
     *
     * @return Logger
     */
    public function newInstance(
        string $name,
        array $adapters = [],
        DateTimeZone $timezone = null
    ): Logger {
        return new Logger($name, $adapters, $timezone);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }
}
