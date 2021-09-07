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
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Di\Traits\InjectionAwareTrait;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;

/**
 * Base class for Phalcon\Cli\Console and Phalcon\Mvc\Application.
 *
 * Class AbstractApplication
 *
 * @package Phalcon\Application
 *
 * @property string $defaultModule
 * @property array  $modules
 */
abstract class AbstractApplication implements InjectionAwareInterface, EventsAwareInterface
{
    use EventsAwareTrait;
    use InjectionAwareTrait;

    /**
     * @var string|null
     */
    protected ?string $defaultModule = null;

    /**
     * @var array
     */
    protected array $modules = [];

    /**
     * AbstractApplication constructor.
     *
     * @param DiInterface|null $container
     */
    public function __construct(DiInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the default module name
     *
     * @return string|null
     */
    public function getDefaultModule(): ?string
    {
        return $this->defaultModule;
    }

    /**
     * Gets the module definition registered in the application via module name
     *
     * @param string $name
     *
     * @return array|object
     * @throws Exception
     */
    public function getModule(string $name)
    {
        if (true !== isset($this->modules[$name])) {
            throw new Exception(
                'Module "' . $name
                . '" is not registered in the application container'
            );
        }

        return $this->modules[$name];
    }

    /**
     * Return the modules registered in the application
     *
     * @return array
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
     * @param array $modules
     * @param bool  $merge
     *
     * @return $this
     */
    public function registerModules(
        array $modules,
        bool  $merge = false
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
