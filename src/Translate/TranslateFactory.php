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

namespace Phiz\Translate;

use Phiz\Config\ConfigInterface;
use Phiz\Support\Traits\ConfigTrait;
use Phiz\Support\Traits\FactoryTrait;
use Phiz\Translate\Adapter\AdapterInterface;
use Phiz\Translate\Adapter\Csv;
use Phiz\Translate\Adapter\Gettext;
use Phiz\Translate\Adapter\NativeArray;

/**
 * Class TranslateFactory
 *
 * @package Phiz\Translate
 *
 * @property InterpolatorFactory $interpolator
 */
class TranslateFactory
{
    use ConfigTrait;
    use FactoryTrait;

    /**
     * @var InterpolatorFactory
     */
    private InterpolatorFactory $interpolator;

    /**
     * AdapterFactory constructor.
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
     */
    public function load($config)
    {
        $config  = $this->checkConfig($config);
        $name    = $config['adapter'];
        $options = $config['options'] ?? [];

        return $this->newInstance($name, $options);
    }

    /**
     * Create a new instance of the adapter
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
            'array'   => NativeArray::class
        ];
    }
}
