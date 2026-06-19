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

namespace Phalcon\Mvc;

use Phalcon\Di\DiInterface;

/**
 * This interface must be implemented by class module definitions
 */
interface ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface|null $container
     *
     * @return void
     */
    public function registerAutoloaders(DiInterface | null $container = null);

    /**
     * Registers services related to the module
     *
     * @param DiInterface $container
     *
     * @return void
     */
    public function registerServices(DiInterface $container);
}
