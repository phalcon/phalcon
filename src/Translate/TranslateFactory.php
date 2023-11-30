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

use Exception as BaseException;
use Phalcon\Config\ConfigInterface;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\Traits\ConfigTrait;
use Phalcon\Traits\Factory\FactoryTrait;
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
    use ConfigTrait;
    use FactoryTrait;

    /**
     * AdapterFactory constructor.
     *
     * @param InterpolatorFactory $interpolator
     * @param array               $services
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
     * @param array|ConfigInterface $config = [
     *                                      'adapter' => 'ini,
     *                                      'options' => [
     *                                      'content'       => '',
     *                                      'delimiter'     => ';',
     *                                      'enclosure'     => '"',
     *                                      'locale'        => '',
     *                                      'defaultDomain' => '',
     *                                      'directory'     => '',
     *                                      'category'      => ''
     *                                      'triggerError'  => false
     *                                      ]
     *                                      ]
     *
     * @return AdapterInterface
     * @throws SupportException
     * @throws BaseException
     */
    public function load(array | ConfigInterface $config): AdapterInterface
    {
        $config  = $this->checkConfig($config);
        $name    = $config['adapter'];
        $options = $config['options'] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     * @param array  $options
     *
     * @return AdapterInterface
     * @throws BaseException
     */
    public function newInstance(string $name, array $options = []): AdapterInterface
    {
        $definition = $this->getService($name);

        return new $definition($this->interpolator, $options);
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
            'csv'     => Csv::class,
            'gettext' => Gettext::class,
            'array'   => NativeArray::class,
        ];
    }
}
