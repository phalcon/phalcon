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

use Exception as BaseException;
use Phalcon\Dispatcher\AbstractDispatcher as BaseDispatcher;
use Phalcon\Dispatcher\Exception as DispatcherException;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Mvc\Dispatcher\Exception;

/**
 * Dispatching is the process of taking the request object, extracting the
 * module name, controller name, action name, and optional parameters contained
 * in it, and then instantiating a controller and calling an action of that
 * controller.
 *
 *```php
 * $di = new \Phalcon\Di\Di();
 *
 * $dispatcher = new \Phalcon\Mvc\Dispatcher();
 *
 * $dispatcher->setDI($di);
 *
 * $dispatcher->setControllerName("posts");
 * $dispatcher->setActionName("index");
 * $dispatcher->setParams([]);
 *
 * $controller = $dispatcher->dispatch();
 *```
 */
class Dispatcher extends BaseDispatcher implements DispatcherInterface
{
    use EventsAwareTrait;

    /**
     * @var string
     */
    protected string $defaultAction = "index";

    /**
     * @var string
     */
    protected string $defaultHandler = "index";

    /**
     * @var string
     */
    protected string $handlerSuffix = "Controller";

    /**
     * Forwards the execution flow to another controller/action.
     *
     * ```php
     * use Phalcon\Events\Event;
     * use Phalcon\Mvc\Dispatcher;
     * use App\Backend\Bootstrap as Backend;
     * use App\Frontend\Bootstrap as Frontend;
     *
     * // Registering modules
     * $modules = [
     *     "frontend" => [
     *         "className" => Frontend::class,
     *         "path"      => __DIR__ . "/app/Modules/Frontend/Bootstrap.php",
     *         "metadata"  => [
     *             "controllersNamespace" => "App\Frontend\Controllers",
     *         ],
     *     ],
     *     "backend" => [
     *         "className" => Backend::class,
     *         "path"      => __DIR__ . "/app/Modules/Backend/Bootstrap.php",
     *         "metadata"  => [
     *             "controllersNamespace" => "App\Backend\Controllers",
     *         ],
     *     ],
     * ];
     *
     * $application->registerModules($modules);
     *
     * // Setting beforeForward listener
     * $eventsManager  = $di->getShared("eventsManager");
     *
     * $eventsManager->attach(
     *     "dispatch:beforeForward",
     *     function(Event $event, Dispatcher $dispatcher, array $forward) use ($modules) {
     *         $metadata = $modules[$forward["module"]]["metadata"];
     *
     *         $dispatcher->setModuleName(
     *             $forward["module"]
     *         );
     *
     *         $dispatcher->setNamespaceName(
     *             $metadata["controllersNamespace"]
     *         );
     *     }
     * );
     *
     * // Forward
     * $this->dispatcher->forward(
     *     [
     *         "module"     => "backend",
     *         "controller" => "posts",
     *         "action"     => "index",
     *     ]
     * );
     * ```
     *
     * @param array $forward
     *
     * @return void
     * @throws EventsException
     * @throws DispatcherException
     */
    public function forward(array $forward): void
    {
        $this->fireManagerEvent("dispatch:beforeForward", $forward);

        parent::forward($forward);
    }

    /**
     * Returns the active controller in the dispatcher
     *
     * @return ControllerInterface|null
     */
    public function getActiveController(): ControllerInterface | null
    {
        return $this->activeHandler;
    }

    /**
     * Possible controller class name that will be located to dispatch the
     * request
     *
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->getHandlerClass();
    }

    /**
     * Gets last dispatched controller name
     *
     * @return string
     */
    public function getControllerName(): string
    {
        return $this->handlerName;
    }

    /**
     * Returns the latest dispatched controller
     *
     * @return ControllerInterface|null
     */
    public function getLastController(): ControllerInterface | null
    {
        return $this->lastHandler;
    }

    /**
     * Gets previous dispatched action name
     *
     * @return string
     */
    public function getPreviousActionName(): string
    {
        return $this->previousActionName;
    }

    /**
     * Gets previous dispatched controller name
     *
     * @return string
     */
    public function getPreviousControllerName(): string
    {
        return $this->previousHandlerName;
    }

    /**
     * Gets previous dispatched namespace name
     *
     * @return string
     */
    public function getPreviousNamespaceName(): string
    {
        return $this->previousNamespaceName;
    }

    /**
     * Sets the controller name to be dispatched
     *
     * @param string $controllerName
     *
     * @return DispatcherInterface
     */
    public function setControllerName(string $controllerName): DispatcherInterface
    {
        $this->handlerName = $controllerName;

        return $this;
    }

    /**
     * Sets the default controller suffix
     *
     * @param string $controllerSuffix
     *
     * @return DispatcherInterface
     */
    public function setControllerSuffix(string $controllerSuffix): DispatcherInterface
    {
        $this->handlerSuffix = $controllerSuffix;

        return $this;
    }

    /**
     * Sets the default controller name
     *
     * @param string $controllerName
     *
     * @return DispatcherInterface
     */
    public function setDefaultController(string $controllerName): DispatcherInterface
    {
        $this->defaultHandler = $controllerName;

        return $this;
    }

    /**
     * Handles a user exception
     *
     * @param BaseException $exception
     *
     * @return false|void
     * @throws EventsException
     */
    protected function handleException(BaseException $exception)
    {
        if (false === $this->fireManagerEvent("dispatch:beforeException", $exception)) {
            return false;
        }
    }

    /**
     * Throws an internal exception
     *
     * @param string $message
     * @param int    $exceptionCode
     *
     * @return false
     * @throws EventsException
     * @throws Exception
     */
    protected function throwDispatchException(string $message, int $exceptionCode = 0): bool
    {
        $this->checkContainer(
            Exception::class,
            "the 'response' service",
            Exception::EXCEPTION_NO_DI
        );

        $response = $this->container->getShared("response");

        /**
         * Dispatcher exceptions automatically sends a 404 status
         */
        $response->setStatusCode(404, "Not Found");

        /**
         * Create the real exception
         */
        $exception = new Exception($message, $exceptionCode);

        if (false === $this->handleException($exception)) {
            return false;
        }

        /**
         * Throw the exception if it wasn't handled
         */
        throw $exception;
    }
}
