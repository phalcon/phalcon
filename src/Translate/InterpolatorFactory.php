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

use Phalcon\Traits\Factory\FactoryTrait;
use Phalcon\Translate\Exceptions\InterpolatorNotRegistered;
use Phalcon\Translate\Interpolator\AssociativeArray;
use Phalcon\Translate\Interpolator\IndexedArray;
use Phalcon\Translate\Interpolator\InterpolatorInterface;

class InterpolatorFactory
{
    use FactoryTrait;

    /**
     * @phpstan-param array<string, string> $services
     */
    public function __construct(array $services = [])
    {
        $this->init($services);
    }

    /**
     * Create a new instance of the adapter
     *
     * @param string $name
     *
     * @return InterpolatorInterface
     */
    public function newInstance(string $name): InterpolatorInterface
    {
        return $this->getCachedInstance($name);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return InterpolatorNotRegistered::class;
    }

    /**
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            'associativeArray' => AssociativeArray::class,
            'indexedArray'     => IndexedArray::class,
        ];
    }
}
