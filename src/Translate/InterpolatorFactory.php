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

use Phalcon\Support\Traits\FactoryTrait;
use Phalcon\Translate\Interpolator\AssociativeArray;
use Phalcon\Translate\Interpolator\IndexedArray;
use Phalcon\Translate\Interpolator\InterpolatorInterface;

class InterpolatorFactory
{
    use FactoryTrait;

    /**
     * InterpolatorFactor constructor.
     *
     * @param array $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     */
    public function newInstance(string $name): InterpolatorInterface
    {
        $definition = $this->getService($name);

        return new $definition($definition);
    }

    protected function getServices(): array
    {
        return [
            'associativeArray' => AssociativeArray::class,
            'indexedArray'     => IndexedArray::class,
        ];
    }
}
