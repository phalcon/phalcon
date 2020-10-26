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
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\Traits\FactoryTrait;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\Csv;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\Adapter\NativeArray;

/**
 * Class TranslateFactory
 *
 * @package Phalcon\Translate
 *
 * @property InterpolatorFactory $interpolator
 */
class TranslateFactory
{
    use FactoryTrait;

    /**
     * @var InterpolatorFactory
     */
    private InterpolatorFactory $interpolator;

    /**
     * TranslateFactory constructor.
     *
     * @param InterpolatorFactory $interpolator
     * @param array               $services
     */
    public function __construct(InterpolatorFactory $interpolator, array $services = [])
    {
        $this->interpolator = $interpolator;

        $this->init($services);
    }

    /**
     * Factory to create an instance from a Config object
     *
     * @param array|ConfigInterface $config = [
     *     'adapter' => 'ini,
     *     'options' => [
     *         'content'       => '',
     *         'delimiter'     => ';',
     *         'enclosure'     => '"',
     *         'locale'        => '',
     *         'defaultDomain' => '',
     *         'directory'     => '',
     *         'category'      => ''
     *         'triggerError'  => false
     *     ]
     * ]
     *
     * @return AdapterInterface
     * @throws Exception
     * @throws SupportException
     */
    public function load($config): AdapterInterface
    {
        $config  = $this->checkConfig($config);
        $name    = $config['adapter'];
        $options = $config['options'] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     */
    /**
     * @param string $name
     * @param array  $options
     *
     * @return AdapterInterface
     * @throws SupportException
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);

        return new $definition($this->interpolator, $options);
    }

    /**
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

    /**
     * Checks the config if it is a valid object
     *
     * @param mixed $config
     *
     * @return array
     * @throws Exception
     */
    private function checkConfig($config): array
    {
        if (true === is_object($config) && $config instanceof ConfigInterface) {
            $config = $config->toArray();
        }

        if (true !== is_array($config)) {
            throw new Exception(
                'Config must be array or Phalcon\Config\Config object'
            );
        }

        if (true !== isset($config['adapter'])) {
            throw new Exception(
                'You must provide "adapter" option in factory config parameter.'
            );
        }

        return $config;
    }
}
