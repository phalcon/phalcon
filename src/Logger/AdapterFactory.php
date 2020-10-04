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

use Phalcon\Logger\Adapter\AdapterInterface;
use Phalcon\Logger\Adapter\Noop;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Adapter\Syslog;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Support\Traits\FactoryTrait;

/**
 * Class AdapterFactory
 *
 * @package Phalcon\Logger
 */
class AdapterFactory
{
    use FactoryTrait;

    /**
     * FactoryTrait constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     * @param string $fileName
     * @param array  $options
     *
     * @return AdapterInterface
     * @throws SupportException
     */
    public function newInstance(
        string $name,
        string $fileName,
        array $options = []
    ): AdapterInterface {
        $definition = $this->getService($name);

        return new $definition($fileName, $options);
    }

    /**
     * @return array
     */
    protected function getServices(): array
    {
        return [
            'noop'   => Noop::class,
            'stream' => Stream::class,
            'syslog' => Syslog::class,
        ];
    }
}
