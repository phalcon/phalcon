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

namespace Phiz\Di;

/**
 * This interface must be implemented in those classes that uses internally the
 * Phiz\Di that creates them
 */
interface InjectionAwareInterface
{
    /**
     * Sets the dependency injector
     *
     * @param DiInterface $container
     */
    public function setDI(DiInterface $container);

    /**
     * Returns the internal dependency injector
     */
    public function getDI(): DiInterface;
}
