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

namespace Phalcon\Helper\Traits;

use Phalcon\Helper\Exception;

use function array_merge;

/**
 * Class AbstractFactory
 *
 * @property array $mapper
 */
trait FactoryTrait
{
    /**
     * @var array
     */
    private array $mapper = [];

    /**
     * Returns a service based on the name; throws exception if it does not
     * exist
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    protected function getService(string $name)
    {
        if (!isset($this->mapper[$name])) {
            throw new Exception('Service ' . $name . ' is not registered');
        }

        return $this->mapper[$name];
    }

    /**
     * Returns the adapters for the factory
     */
    abstract protected function getAdapters(): array;

    /**
     * AdapterFactory constructor.
     *
     * @param array $services
     */
    protected function init(array $services = []): void
    {
        $this->mapper = array_merge($this->getAdapters(), $services);
    }
}
