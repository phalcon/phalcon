<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Application;

use Phiz\Di\DiInterface;
use Phiz\Di\Injectable;
use Phiz\Events\EventsAwareInterface;
use Phiz\Events\ManagerInterface;

/**
 * Base class for Phiz\Cli\Console and Phiz\Mvc\Application.
 */
abstract class AbstractApplication extends Injectable implements EventsAwareInterface
{
    public function __construct( ?DiInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * @var string
     */
    protected $defaultModule;

    /**
     * @var null | ManagerInterface
     */
    protected $eventsManager;

    /**
     * @var array
     */
    protected $modules = [];

    /**
     * Phiz\AbstractApplication constructor
     */


    /**
     * Returns the default module name
     */
    public function getDefaultModule() : string
    {
        return $this->defaultModule;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager()  : ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * Gets the module definition registered in the application via module name
     * TODO: return array | object
     */
    public function getModule(string $name) 
    {
        //var module;
        $module = $this->modules[$name] ?? null;
        
        if (is_null($module)) {
            throw new Exception(
                "Module '" . $name . "' isn't registered in the application container"
            );
        }

        return $module;
    }

    /**
     * Return the modules registered in the application
     */
    public function getModules() : array
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
     */
    public function registerModules(array $modules, bool $merge = false)  : AbstractApplication
    {
        if ($merge) {
             $this->modules = array_merge($this->modules, $modules);
        } else {
            $this->modules = $modules;
        }

        return $this;
    }

    /**
     * Sets the module name to be used if the router doesn't return a valid module
     */
    public function setDefaultModule(string $defaultModule) : AbstractApplication
    {
         $this->defaultModule = $defaultModule;

        return $this;
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager( ManagerInterface $eventsManager) : void
    {
        $this->eventsManager = $eventsManager;
    }
}

