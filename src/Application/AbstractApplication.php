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

use Phalcon\Application\Exceptions\ModuleNotRegistered;
use Phalcon\Contracts\Container\Service\Collection;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
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
     * @param DiInterface|Collection|null $container
     */
    public function __construct(DiInterface | Collection | null $container = null)
    {
        if (null !== $container) {
            $this->container = $container;
        }
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
    public function getModule(string $name)
    {
        if (!isset($this->modules[$name])) {
            throw new ModuleNotRegistered($name);
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
    ): static {
        if (true === $merge) {
            $this->modules = array_merge($this->modules, $modules);
        } else {
            $this->modules = $modules;
        }

        return $this;
    }

    /**
     * Sets the module name to be used if the router does not return a valid
     * module
     *
     * @param string $defaultModule
     *
     * @return $this
     */
    public function setDefaultModule(string $defaultModule): static
    {
        $this->defaultModule = $defaultModule;

        return $this;
    }

    /**
     * Sets the events manager
     *
     * @param ManagerInterface $eventsManager
     */
    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        $this->getDI()->set('eventsManager', $eventsManager);
        $this->eventsManager = $eventsManager;
    }
}
