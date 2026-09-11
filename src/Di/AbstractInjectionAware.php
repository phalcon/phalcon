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

namespace Phalcon\Di;

use stdClass;

/**
 * This abstract class offers common access to the DI in a class
 */
abstract class AbstractInjectionAware extends stdClass implements InjectionAwareInterface
{
    /**
     * Dependency Injector
     */
    protected DiInterface | null $container = null;

    /**
     * Returns the internal dependency injector
     */
    public function getDI(): DiInterface | null
    {
        return $this->container;
    }

    /**
     * Sets the dependency injector
     */
    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }
}
