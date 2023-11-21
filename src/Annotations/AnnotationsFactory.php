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

namespace Phalcon\Annotations;

use Exception as BaseException;
use Phalcon\Annotations\Adapter\AdapterInterface;
use Phalcon\Annotations\Adapter\Apcu;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Adapter\Stream;
use Phalcon\Config\Config;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\Traits\ConfigTrait;
use Phalcon\Traits\Factory\FactoryTrait;

/**
 * Factory to create annotations components
 */
class AnnotationsFactory
{
    use FactoryTrait;
    use ConfigTrait;

    /**
     * AdapterFactory constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|Config $config = [
     *                             'adapter' => 'apcu',
     *                             'options' => [
     *                             'prefix' => 'phalcon',
     *                             'lifetime' => 3600,
     *                             'annotationsDir' => 'phalconDir'
     *                             ]
     *                             ]
     *
     * @return AdapterInterface
     * @throws SupportException
     */
    public function load(array|Config $config): AdapterInterface
    {
        $config = $this->checkConfig($config);
        $config = $this->checkConfigElement($config, "adapter");
        $name   = $config["adapter"];

        unset($config["adapter"]);

        $options = $config["options"] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     * @param array  $options = [
     *                        'prefix' => 'phalcon',
     *                        'lifetime' => 3600,
     *                        'annotationsDir' => 'phalconDir'
     *                        ]
     *
     * @return AdapterInterface
     * @throws BaseException
     */
    public function newInstance(
        string $name,
        array $options = []
    ): AdapterInterface {
        $definition = $this->getService($name);

        return new $definition($options);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available adapters
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            "apcu"   => Apcu::class,
            "memory" => Memory::class,
            "stream" => Stream::class,
        ];
    }
}
