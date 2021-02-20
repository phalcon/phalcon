<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Cli;

use Phalcon\Application\AbstractApplication;
use Phalcon\Cli\Router\Route;
use Phalcon\Cli\Console\Exception;
use Phalcon\Di\DiInterface;
use Phalcon\Events\ManagerInterface;

/**
 * This component allows to create CLI applications using Phalcon
 */
class Console extends AbstractApplication
{
    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * Handle the whole command-line tasks
     */
    public function handle(array $arguments = null)
    {
        $container = $this->container;

        if (!is_object($container)) {
            throw new Exception(
                Exception::containerServiceNotFound("internal services")
            );
        }

        $eventsManager = $this->eventsManager;

        /**
         * Call boot event, this allows the developer to perform initialization
         * actions
         */
        if (is_object($eventsManager)) {
            if ($eventsManager->fire("console:boot", $this) === false) {
                return false;
            }
        }

        $router = $container->getShared("router");

        if (!count($arguments) && $this->arguments) {
            $router->handle($this->arguments);
        } else {
            $router->handle($arguments);
        }

        /**
         * If the router doesn't return a valid module we use the default module
         */
        $moduleName = $router->getModuleName();

        if (empty($moduleName)) {
            $moduleName = $this->defaultModule;
        }

        if ($moduleName) {
            if (is_object($eventsManager)){
                if ($eventsManager->fire("console:beforeStartModule", $this, $moduleName) === false) {
                    return false;
                }
            }

            $modules = $this->modules;

            if (!isset($modules[$moduleName])) {
                throw new Exception(
                    "Module '" . $moduleName . "' isn't registered in the console container"
                );
            }

            $module = $modules[$moduleName];

            if (!is_array($module)) {
                throw new Exception("Invalid module definition path");
            }
            $className = $module["className"] ?? "Module";
            $path = $module["path"] ?? null;
            if ($path!==null) {
                if (!file_exists($path)) {
                    throw new Exception(
                        "Module definition path '" . $path . "' doesn't exist"
                    );
                }

                if (!class_exists($className, false)) {
                    require $path;
                }
            }

            $moduleObject = $container->get($className);

            $moduleObject->registerAutoloaders($container);
            $moduleObject->registerServices($container);

            if (is_object($eventsManager)){
                if ($eventsManager->fire("console:afterStartModule", $this, $moduleObject) === false) {
                    return false;
                }
            }

        }

        $dispatcher = $container->getShared("dispatcher");

        $dispatcher->setModuleName($router->getModuleName());
        $dispatcher->setTaskName($router->getTaskName());
        $dispatcher->setActionName($router->getActionName());
        $dispatcher->setParams($router->getParams());
        $dispatcher->setOptions($this->options);

        if (is_object($eventsManager)){
            if ($eventsManager->fire("console:beforeHandleTask", $this, $dispatcher) === false) {
                return false;
            }
        }

        $task = $dispatcher->dispatch();

        if (is_object($eventsManager)){
            $eventsManager->fire("console:afterHandleTask", $this, $task);
        }

        return $task;
    }

    /**
     * Set an specific argument
     */
    public function setArgument(array $arguments = null, bool $str = true, bool $shift = true) : Console
    {
        $args = [];
        $opts = [];
        $handleArgs = [];

        if (shift && count($arguments)) {
            array_shift($arguments);
        }

        foreach( $arguments as $arg)  {
            if (is_string($arg)) {
                if (strncmp($arg, "--", 2) === 0) {
                    $pos = strpos($arg, "=");
                    if ($pos !== false) {
                        $opts[trim(substr($arg, 2, $pos - 2))] = trim(substr($arg, $pos + 1));
                    } else {
                        $opts[trim(substr($arg, 2))] = true;
                    }
                } else {
                    if (strncmp($arg, "-", 1) === 0) {
                        $opts[substr($arg, 1)] = true;
                    } else {
                        $args[] = $arg;
                    }
                }
            } else {
                $args[] = $arg;
            }
        }

        if ($str) {
            $this->arguments = implode(
                Route::getDelimiter(),
                $args
            );
        } else {
            if (count($args)) {
                $handleArgs["task"] = array_shift($args);
            }

            if (count($args)) {
                $handleArgs["action"] = array_shift($args);
            }

            if (count($args)) {
                $handleArgs = array_merge($handleArgs, $args);
            }

            $this->arguments = $handleArgs;
        }

        $this->options = $opts;

        return $this;
    }
}
