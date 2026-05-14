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
use Phalcon\Support\Traits\ConfigTrait;
use Phalcon\Traits\Factory\FactoryTrait;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\Csv;
use Phalcon\Translate\Adapter\Gettext;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\Exceptions\TranslatorNotRegistered;

/**
 * @property InterpolatorFactory $interpolator
 *
 * @psalm-type TConfig array{
 *      adapter: string,
 *      options?: array{
 *          content: string,
 *          delimiter: string,
 *          enclosure: string,
 *          locale: string,
 *          defaultDomain: string,
 *          directory: string,
 *          category: string,
 *          triggerError: bool,
 *      }
 *  }
 */
class TranslateFactory
{
    use ConfigTrait;
    use FactoryTrait;

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
     * @param ConfigInterface|TConfig $config
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function load(array | ConfigInterface $config): AdapterInterface
    {
        /** @var TConfig $config */
        $config  = $this->checkConfig($config);
        $name    = (string)$config['adapter'];
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
        return $this->getCachedInstance($name, $this->interpolator, $options);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return TranslatorNotRegistered::class;
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
