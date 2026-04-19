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

namespace Phalcon\Di\Traits;

/**
 * This abstract class offers common access to the DI in a class
 *
 * Class AbstractInjectionAware
 *
 * @package Phalcon\Di
 *
 * @property object $container
 */
trait InjectionAwareTrait
{
    /**
     * Dependency Injector
     *
     * @var object|null
     */
    protected object | null $container = null;

    /**
     * Returns the internal dependency injector
     */
    public function getDI(): object | null
    {
        return $this->container;
    }

    /**
     * Sets the dependency injector
     */
    public function setDI(object $container): void
    {
        $this->container = $container;
    }

    /**
     * @param string $exceptionClass
     *
     * @return void
     */
    protected function checkContainer(
        string $exceptionClass,
        string $message,
        int $code = 0
    ): void {
        if (null === $this->container) {
            throw new $exceptionClass(
                'A dependency injection container is required to access '
                . $message,
                $code
            );
        }
    }
}
