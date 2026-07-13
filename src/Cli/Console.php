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

namespace Phalcon\Cli;

use Closure;
use Phalcon\Application\AbstractApplication;
use Phalcon\Cli\Console\Exception;
use Phalcon\Cli\Console\Exceptions\ContainerRequired;
use Phalcon\Cli\Console\Exceptions\InvalidModuleDefinition;
use Phalcon\Cli\Console\Exceptions\ModuleDefinitionPathNotFound;
use Phalcon\Cli\Router\Route;
use Phalcon\Di\DiInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Traits\Php\FileTrait;

use function array_merge;
use function array_shift;
use function call_user_func_array;
use function class_exists;
use function implode;
use function is_array;
use function is_object;
use function is_string;
use function strncmp;
use function strpos;
use function substr;
use function trim;

/**
 * This component allows to create CLI applications using Phalcon
 */
class Console extends AbstractApplication
{
    use FileTrait;

    /**
     * @var array|string
     */
    protected array | string $arguments = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * Handle the whole command-line tasks
     *
     * @param array $arguments
     *
     * @return bool|mixed|null
     * @throws Exception
     * @throws Router\Exception
     * @throws EventsException
     */
    public function handle(array $arguments = [])
    {
        if (null === $this->container) {
            throw new ContainerRequired();
        }

        /**
         * Call boot event, this allows the developer to perform initialization
         * actions
         */
        if (false === $this->fireManagerEvent("console:boot")) {
            return false;
        }

        /** @var Router $router */
        if ($this->container instanceof DiInterface) {
            $router = $this->container->getShared("router");
        } else {
            $router = $this->container->get("router");
        }

        if (empty($arguments) && !empty($this->arguments)) {
            $router->handle($this->arguments);
        } else {
            $router->handle($arguments);
        }

        /**
         * If the router does not return a valid module we use the default module
         */
        $moduleName = $router->getModuleName();

        if (empty($moduleName)) {
            $moduleName = $this->defaultModule;
        }

        if (!empty($moduleName)) {
            if (false === $this->fireManagerEvent("console:beforeStartModule", $moduleName)) {
                return false;
            }

            /**
             * Gets the module definition
             */
            $module = $this->getModule($moduleName);

            /**
             * A module definition must be an array or an object
             */
            if (!is_array($module) && !is_object($module)) {
                throw new InvalidModuleDefinition(
                    $moduleName,
                    "The module definition must be an array or an object"
                );
            }

            /**
             * An array module definition contains a path to a module
             * definition class
             */
            if (is_array($module)) {
                /**
                 * Class name used to load the module definition
                 */
                $className = $module["className"] ?? "Module";

                /**
                 * If developer specify a path try to include the file
                 */
                if (isset($module["path"])) {
                    $path = $module["path"];
                    if (true !== $this->phpFileExists($path)) {
                        throw new ModuleDefinitionPathNotFound($path);
                    }

                    if (true !== class_exists($className, false)) {
                        require_once $path;
                    }
                }

                $moduleObject = $this->container->get($className);

                /**
                 * 'registerAutoloaders' and 'registerServices' are
                 * automatically called
                 */
                $moduleObject->registerAutoloaders($this->container);
                $moduleObject->registerServices($this->container);
            } else {
                /**
                 * A module definition object, can be a Closure instance
                 */
                if (!($module instanceof Closure)) {
                    throw new InvalidModuleDefinition(
                        $moduleName,
                        "The module definition object must be a Closure"
                    );
                }

                $moduleObject = call_user_func_array(
                    $module,
                    [
                        $this->container,
                    ]
                );
            }

            /**
             * The "afterStartModule" event is fired once the module has
             * started. Unlike Phalcon\Mvc\Application - where the return value
             * is a notification only - Console honors a `false` return and
             * aborts handling. This divergence is retained for backward
             * compatibility and is unified in the next major version.
             */
            if (false === $this->fireManagerEvent("console:afterStartModule", $moduleObject)) {
                return false;
            }
        }

        /** @var Dispatcher $dispatcher */
        if ($this->container instanceof DiInterface) {
            $dispatcher = $this->container->getShared("dispatcher");
        } else {
            $dispatcher = $this->container->get("dispatcher");
        }

        $dispatcher->setModuleName($moduleName);
        $dispatcher->setTaskName($router->getTaskName());
        $dispatcher->setActionName($router->getActionName());
        $dispatcher->setParams($router->getParameters());
        $dispatcher->setOptions($this->options);

        if (false === $this->fireManagerEvent("console:beforeHandleTask", $dispatcher)) {
            return false;
        }

        $task = $dispatcher->dispatch();

        $this->fireManagerEvent("console:afterHandleTask", $task);

        return $task;
    }

    /**
     * Set a specific argument
     *
     * @param array $arguments
     * @param bool  $str
     * @param bool  $shift
     *
     * @return $this
     */
    public function setArgument(
        array $arguments = [],
        bool $str = true,
        bool $shift = true
    ): static {
        $args       = [];
        $opts       = [];
        $handleArgs = [];

        if (true === $shift && !empty($arguments)) {
            array_shift($arguments);
        }

        foreach ($arguments as $argument) {
            if (is_string($argument)) {
                if (0 === strncmp($argument, "--", 2)) {
                    $pos = strpos($argument, "=");

                    if (false !== $pos) {
                        $opts[trim(substr($argument, 2, $pos - 2))] = trim(substr($argument, $pos + 1));
                    } else {
                        $opts[trim(substr($argument, 2))] = true;
                    }
                } elseif (0 === strncmp($argument, "-", 1)) {
                    $opts[substr($argument, 1)] = true;
                } else {
                    $args[] = $argument;
                }
            } else {
                $args[] = $argument;
            }
        }

        if (true === $str) {
            $this->arguments = implode(Route::getDelimiter(), $args);
        } else {
            if (!empty($args)) {
                $handleArgs["task"] = array_shift($args);
            }

            if (!empty($args)) {
                $handleArgs["action"] = array_shift($args);
            }

            if (!empty($args)) {
                $handleArgs = array_merge($handleArgs, $args);
            }

            $this->arguments = $handleArgs;
        }

        $this->options = $opts;

        return $this;
    }
}
