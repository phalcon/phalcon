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

namespace Phalcon\Translate;

use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Translate\TranslateTypes;
use Phalcon\Factory\AbstractFactory;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\Csv;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Exceptions\TranslatorNotRegistered;

/**
 * @property InterpolatorFactory $interpolator
 *
 * @phpstan-import-type translate_factory_config from TranslateTypes
 */
class TranslateFactory extends AbstractFactory
{
    /**
     * @phpstan-param array<string, string> $services
     */
    public function __construct(
        private InterpolatorFactory $interpolator,
        array $services = []
    ) {
        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @phpstan-param ConfigInterface|translate_factory_config $config
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function load(mixed $config): AdapterInterface
    {
        $config = $this->checkConfig($config);

        /** @phpstan-var translate_factory_config $config */
        $config = $this->checkConfigElement($config, "adapter");

        $name = (string)$config['adapter'];

        /** @var array<string, mixed> $options */
        $options = isset($config['options']) ? (array)$config['options'] : [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @phpstan-param array<string, mixed> $options
     *
     * @return AdapterInterface
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        /** @var class-string<AdapterInterface> $definition */
        $definition = $this->getService($name);

        return new $definition($this->interpolator, $options);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return TranslatorNotRegistered::class;
    }

    /**
     * Returns the available adapters
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            'csv'     => Csv::class,
            'gettext' => Gettext::class,
            'array'   => NativeArray::class,
        ];
    }
}
