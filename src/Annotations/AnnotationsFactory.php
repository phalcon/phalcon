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

use Phalcon\Annotations\Adapter\AdapterInterface;
use Phalcon\Annotations\Adapter\Apcu;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Adapter\Stream;
use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Annotations\AnnotationsTypes;
use Phalcon\Factory\AbstractFactory;

/**
 * Factory to create annotations components
 *
 * @phpstan-import-type annotations_options from AnnotationsTypes
 */
class AnnotationsFactory extends AbstractFactory
{
    /**
     * AdapterFactory constructor.
     *
     * @phpstan-param array<string, class-string<AdapterInterface>> $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * @param array|ConfigInterface $config = [
     *                                      'adapter' => 'apcu',
     *                                      'options' => [
     *                                      'prefix' => 'phalcon',
     *                                      'lifetime' => 3600,
     *                                      'annotationsDir' => 'phalconDir'
     *                                      ]
     *                                      ]
     *
     * Factory to create an instance from a Config object
     *
     * @throws \Exception
     *
     * @phpstan-param array<string, mixed>|ConfigInterface $config
     */
    public function load(mixed $config): AdapterInterface
    {
        $config = $this->checkConfig($config);
        $config = $this->checkConfigElement($config, "adapter");

        /** @var string $name */
        $name = $config["adapter"];

        unset($config["adapter"]);

        /** @var annotations_options $options */
        $options = $config["options"] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param array $options = [
     *                       'prefix' => 'phalcon',
     *                       'lifetime' => 3600,
     *                       'annotationsDir' => 'phalconDir'
     *                       ]
     *
     * @throws \Exception
     *
     * @phpstan-param annotations_options $options
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);

        /** @var AdapterInterface $adapter */
        $adapter = new $definition($options);

        return $adapter;
    }

    /**
     * @return class-string<\Exception>
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available adapters
     *
     * @return array<string, string>
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
