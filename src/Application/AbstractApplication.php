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

namespace Phalcon\Application;

use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;

/**
 * Base class for Phalcon\Cli\Console and Phalcon\Mvc\Application.
 *
 * @phpstan-type TModule = array{
 *     string: array{
 *          className: string,
 *          path: string,
 *     }
 * }
 */
abstract class AbstractApplication extends Injectable implements EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * @var string
     */
    protected string $defaultModule = '';

    /**
     * @var TModule[]
     */
    protected array $modules = [];

    /**
     * AbstractApplication constructor.
     *
     * @param DiInterface|null $container
     */
    public function __construct(DiInterface | null $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the default module name
     *
     * @return string
     */
    public function getDefaultModule(): string
    {
        return $this->defaultModule;
    }

    /**
     * Gets the module definition registered in the application via module name
     *
     * @param string $name
     *
     * @return TModule
     * @throws Exception
     */
    public function getModule(string $name): array
    {
        if (!isset($this->modules[$name])) {
            throw new Exception(
                "Module '" . $name
                . "' is not registered in the application container"
            );
        }

        return $this->modules[$name];
    }

    /**
     * Return the modules registered in the application
     *
     * @return TModule[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Register an array of modules present in the application
     *
     * ```php
     * $this->registerModules(
     *     [
     *         "frontend" => [
     *             "className" => \Multiple\Frontend\Module::class,
     *             "path"      => "../apps/frontend/Module.php",
     *         ],
     *         "backend" => [
     *             "className" => \Multiple\Backend\Module::class,
     *             "path"      => "../apps/backend/Module.php",
     *         ],
     *     ]
     * );
     * ```
     *
     * @param TModule[] $modules
     * @param bool      $merge
     *
     * @return $this
     */
    public function registerModules(
        array $modules,
        bool $merge = false
    ): AbstractApplication {
        if (true === $merge) {
            $this->modules = array_merge($this->modules, $modules);
        } else {
            $this->modules = $modules;
        }

        return $this;
    }

    /**
     * Sets the module name to be used if the router doesn't return a valid
     * module
     *
     * @param string $defaultModule
     *
     * @return $this
     */
    public function setDefaultModule(string $defaultModule): AbstractApplication
    {
        $this->defaultModule = $defaultModule;

        return $this;
    }
}
