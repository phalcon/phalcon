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

namespace Phalcon\Dispatcher;

use Exception as BaseException;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cli\Dispatcher\Exception as CliDispatcherException;
use Phalcon\Cli\TaskInterface;
use Phalcon\Di\Injectable;
use Phalcon\Dispatcher\Exception as DispatcherException;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Filter\FilterInterface;
use Phalcon\Mvc\ControllerInterface;
use Phalcon\Mvc\Model\BinderInterface;

use function array_map;
use function call_user_func_array;
use function class_exists;
use function is_callable;
use function is_object;
use function is_string;
use function lcfirst;
use function method_exists;
use function preg_split;
use function spl_object_hash;

/**
 * This is the base class for Phalcon\Mvc\Dispatcher and Phalcon\Cli\Dispatcher.
 * This class can't be instantiated directly, you can use it to create your own
 * dispatchers.
 */
abstract class AbstractDispatcher extends Injectable implements DispatcherInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    /**
     * @var string
     */
    protected string $actionName = "";
    /**
     * @var string
     */
    protected string $actionSuffix = "Action";
    /**
     * @var TaskInterface|ControllerInterface|null
     */
    protected TaskInterface | ControllerInterface | null $activeHandler = null;
    /**
     * @var array
     */
    protected array $activeMethodMap = [];
    /**
     * @var array
     */
    protected array $camelCaseMap = [];

    /**
     * @var string
     */
    protected string $defaultAction = "";
    /**
     * @var string
     */
    protected string $defaultHandler = "";
    /**
     * @var string
     */
    protected string $defaultNamespace = "";
    /**
     * @var bool
     */
    protected bool $finished = false;
    /**
     * @var bool
     */
    protected bool $forwarded = false;
    /**
     * @var array
     */
    protected array $handlerHashes = [];
    /**
     * @var string
     */
    protected string $handlerName = "";
    /**
     * @var string
     */
    protected string $handlerSuffix = "";
    /**
     * @var bool
     */
    protected bool $isControllerInitialize = false;

    /**
     * @var mixed|null
     */
    protected mixed $lastHandler = null;

    /**
     * @var BinderInterface|null
     */
    protected BinderInterface | null $modelBinder = null;

    /**
     * @var bool
     */
    protected bool $modelBinding = false;

    /**
     * @var string
     */
    protected string $moduleName = "";

    /**
     * @var string
     */
    protected string $namespaceName = "";

    /**
     * @var array
     */
    protected array $parameters = [];

    /**
     * @var string
     */
    protected string $previousActionName = "";

    /**
     * @var string
     */
    protected string $previousHandlerName = "";

    /**
     * @var string
     */
    protected string $previousNamespaceName = "";

    /**
     * @var mixed|null
     */
    protected mixed $returnedValue = null;

    /**
     * @param mixed  $handler
     * @param string $actionMethod
     * @param array  $parameters
     *
     * @return mixed
     */
    public function callActionMethod(
        mixed $handler,
        string $actionMethod,
        array $parameters = []
    ): mixed {
        return call_user_func_array(
            [$handler, $actionMethod],
            array_values($parameters)
        );
    }

    /**
     * Process the results of the router by calling into the appropriate
     * controller action(s) including any routing data or injected parameters.
     *
     * @return bool|mixed|null Returns the dispatched handler class (the
     *                         Controller for Mvc dispatching or a Task for CLI
     *                         dispatching) or <tt>false</tt> if an exception
     *                         occurred and the operation was stopped by
     *                         returning <tt>false</tt> in the exception handler.
     *
     * @throws BaseException if any uncaught or unhandled exception occurs during
     *                   the dispatcher process.
     */
    public function dispatch()
    {
        if (null === $this->container) {
            $this->throwDispatchException(
                "A dependency injection container is required to access "
                . "related dispatching services",
                DispatcherException::EXCEPTION_NO_DI
            );

            return false;
        }

        $hasEventsManager = (null !== $this->eventsManager);
        $this->finished   = true;

        if (true === $hasEventsManager) {
            try {
                // Calling beforeDispatchLoop event
                // Note: Allow user to forward in the beforeDispatchLoop.
                if (
                    $this->fireManagerEvent("dispatch:beforeDispatchLoop") === false &&
                    false !== $this->finished
                ) {
                    return false;
                }
            } catch (BaseException $ex) {
                // Exception occurred in beforeDispatchLoop.

                /**
                 * The user can optionally forward now in the
                 * `dispatch:beforeException` event or return <tt>false</tt> to
                 * handle the exception and prevent it from bubbling. In the
                 * event the user does forward but does or does not return
                 * false, we assume the forward takes precedence. The returning
                 * false intuitively makes more sense when inside the dispatch
                 * loop and technically we are not here. Therefore, returning
                 * false only impacts whether non-forwarded exceptions are
                 * silently handled or bubbled up the stack. Note that this
                 * behavior is slightly different than other subsequent events
                 * handled inside the dispatch loop.
                 */

                $status = $this->handleException($ex);

                if (false !== $this->finished) {
                    // No forwarding
                    if (false === $status) {
                        return false;
                    }

                    // Otherwise, bubble Exception
                    throw $ex;
                }
                // Otherwise, user forwarded, continue
            }
        }

        $value            = null;
        $handler          = null;
        $numberDispatches = 0;
        $this->finished   = false;

        while (true !== $this->finished) {
            $numberDispatches++;

            // Throw an exception after 256 consecutive forwards
            if (256 === $numberDispatches) {
                $this->throwDispatchException(
                    "Dispatcher has detected a cyclic routing causing "
                    . "stability problems",
                    DispatcherException::EXCEPTION_CYCLIC_ROUTING
                );

                break;
            }

            $this->finished = true;

            $this->resolveEmptyProperties();

            if (true === $hasEventsManager) {
                try {
                    // Calling "dispatch:beforeDispatch" event
                    if (
                        false === $this->fireManagerEvent("dispatch:beforeDispatch") ||
                        false === $this->finished
                    ) {
                        continue;
                    }
                } catch (BaseException $ex) {
                    if (
                        false === $this->handleException($ex) ||
                        false === $this->finished
                    ) {
                        continue;
                    }

                    throw $ex;
                }
            }

            $handlerClass = $this->getHandlerClass();

            /**
             * Handlers are retrieved as shared instances from the Service
             * Container
             */
            $hasService = $this->container->has($handlerClass);
            if (true !== $hasService) {
                /**
                 * DI doesn't have a service with that name, try to load it
                 * using an autoloader
                 */
                $hasService = class_exists($handlerClass);
            }

            // If the service can be loaded we throw an exception
            if (true !== $hasService) {
                $status = $this->throwDispatchException(
                    $handlerClass . " handler class cannot be loaded",
                    DispatcherException::EXCEPTION_HANDLER_NOT_FOUND
                );

                if (
                    false === $status &&
                    false === $this->finished
                ) {
                    continue;
                }

                break;
            }

            $handler = $this->container->getShared($handlerClass);

            // Handlers must be only objects
            if (!is_object($handler)) {
                $status = $this->throwDispatchException(
                    "Invalid handler returned from the services container",
                    DispatcherException::EXCEPTION_INVALID_HANDLER
                );

                if (false === $status && false === $this->finished) {
                    continue;
                }

                break;
            }

            // Check if the handler is new (hasn't been initialized).
            $handlerHash  = spl_object_hash($handler);
            $isNewHandler = !isset($this->handlerHashes[$handlerHash]);

            if (true === $isNewHandler) {
                $this->handlerHashes[$handlerHash] = true;
            }

            $this->activeHandler = $handler;
            $namespaceName       = $this->namespaceName;
            $handlerName         = $this->handlerName;
            $actionName          = $this->actionName;

            // Check if the method exists in the handler
            $actionMethod = $this->getActiveMethod();

            if (!is_callable([$handler, $actionMethod])) {
                if (true === $hasEventsManager) {
                    if (false === $this->fireManagerEvent("dispatch:beforeNotFoundAction")) {
                        continue;
                    }

                    if (false === $this->finished) {
                        continue;
                    }
                }

                /**
                 * Try to throw an exception when an action isn't defined on the
                 * object
                 */
                $status = $this->throwDispatchException(
                    "Action '" . $actionName . "' was not found on handler '"
                    . $handlerName . "'",
                    DispatcherException::EXCEPTION_ACTION_NOT_FOUND
                );

                if (false === $status && false === $this->finished) {
                    continue;
                }

                break;
            }

            /**
             * In order to ensure that the `initialize()` gets called we'll
             * destroy the current handlerClass from the DI container in the
             * event that an error occurs and we continue out of this block.
             * This is necessary because there is a disjoin between retrieval of
             * the instance and the execution of the `initialize()` event. From
             * a coding perspective, it would have made more sense to probably
             * put the `initialize()` prior to the beforeExecuteRoute which
             * would have solved this. However, for posterity, and to remain
             * consistency, we'll ensure the default and documented behavior
             * works correctly.
             */
            if (true === $hasEventsManager) {
                try {
                    // Calling "dispatch:beforeExecuteRoute" event
                    if (
                        false === $this->fireManagerEvent("dispatch:beforeExecuteRoute") ||
                        false === $this->finished
                    ) {
                        $this->container->remove($handlerClass);
                        continue;
                    }
                } catch (BaseException $ex) {
                    if (
                        false === $this->handleException($ex) ||
                        false === $this->finished
                    ) {
                        $this->container->remove($handlerClass);

                        continue;
                    }

                    throw $ex;
                }
            }

            if (true === method_exists($handler, "beforeExecuteRoute")) {
                try {
                    // Calling "beforeExecuteRoute" as direct method
                    if (
                        false === $handler->beforeExecuteRoute($this) ||
                        false === $this->finished
                    ) {
                        $this->container->remove($handlerClass);

                        continue;
                    }
                } catch (BaseException $ex) {
                    if (
                        false === $this->handleException($ex) ||
                        false === $this->finished
                    ) {
                        $this->container->remove($handlerClass);

                        continue;
                    }

                    throw $ex;
                }
            }

            /**
             * Call the "initialize" method just once per request
             *
             * Note: The `dispatch:afterInitialize` event is called regardless
             *       of the presence of an `initialize()` method. The naming is
             *       poor; however, the intent is for a more global "constructor
             *       is ready to go" or similarly "__onConstruct()" methodology.
             *
             * Note: In Phalcon 4.0, the `initialize()` and
             * `dispatch:afterInitialize` event will be handled prior to the
             * `beforeExecuteRoute` event/method blocks. This was a bug in the
             * original design that was not able to change due to widespread
             * implementation. With proper documentation change and blog posts
             * for 4.0, this change will happen.
             *
             * @see https://github.com/phalcon/cphalcon/pull/13112
             */
            if (true === $isNewHandler) {
                if (true === method_exists($handler, "initialize")) {
                    try {
                        $this->isControllerInitialize = true;

                        $handler->initialize();
                    } catch (BaseException $ex) {
                        $this->isControllerInitialize = false;

                        /**
                         * If this is a dispatch exception (e.g. From
                         * forwarding) ensure we don't handle this twice. In
                         * order to ensure this doesn't happen all other
                         * exceptions thrown outside this method in this class
                         * should not call "throwDispatchException" but instead
                         * throw a normal Exception.
                         */
                        if (
                            false === $this->handleException($ex) ||
                            false === $this->finished
                        ) {
                            continue;
                        }

                        throw $ex;
                    }
                }

                $this->isControllerInitialize = false;

                /**
                 * Calling "dispatch:afterInitialize" event
                 */
                if (true === $hasEventsManager) {
                    try {
                        if (
                            false === $this->fireManagerEvent("dispatch:afterInitialize") ||
                            false === $this->finished
                        ) {
                            continue;
                        }
                    } catch (BaseException $ex) {
                        if (
                            false === $this->handleException($ex) ||
                            false === $this->finished
                        ) {
                            continue;
                        }

                        throw $ex;
                    }
                }
            }

            if (true === $this->modelBinding) {
                $modelBinder  = $this->modelBinder;
                $bindCacheKey = "_PHMB_" . $handlerClass . "_" . $actionMethod;

                $this->parameters = $modelBinder->bindToHandler(
                    $handler,
                    $this->parameters,
                    $bindCacheKey,
                    $actionMethod
                );
            }

            /**
             * Calling afterBinding
             */
            if (true === $hasEventsManager) {
                if (false === $this->fireManagerEvent("dispatch:afterBinding")) {
                    continue;
                }

                /**
                 * Check if the user made a forward in the listener
                 */
                if (false === $this->finished) {
                    continue;
                }
            }

            /**
             * Calling afterBinding as callback and event
             */
            if (true === method_exists($handler, "afterBinding")) {
                if (false === $handler->afterBinding($this)) {
                    continue;
                }

                /**
                 * Check if the user made a forward in the listener
                 */
                if (false === $this->finished) {
                    continue;
                }
            }

            /**
             * Save the current handler
             */
            $this->lastHandler = $handler;

            try {
                /**
                 * We update the latest value produced by the latest handler
                 */
                $this->returnedValue = $this->callActionMethod(
                    $handler,
                    $actionMethod,
                    $this->parameters
                );

                if (false === $this->finished) {
                    continue;
                }
            } catch (BaseException $ex) {
                if (
                    false === $this->handleException($ex) ||
                    false === $this->finished
                ) {
                    continue;
                }

                throw $ex;
            }

            /**
             * Calling "dispatch:afterExecuteRoute" event
             */
            if (true === $hasEventsManager) {
                try {
                    if (
                        false === $this->fireManagerEvent("dispatch:afterExecuteRoute", $value) ||
                        false === $this->finished
                    ) {
                        continue;
                    }
                } catch (BaseException $ex) {
                    if (
                        false === $this->handleException($ex) ||
                        false === $this->finished
                    ) {
                        continue;
                    }

                    throw $ex;
                }
            }

            /**
             * Calling "afterExecuteRoute" as direct method
             */
            if (true === method_exists($handler, "afterExecuteRoute")) {
                try {
                    if (
                        false === $handler->afterExecuteRoute($this, $value) ||
                        false === $this->finished
                    ) {
                        continue;
                    }
                } catch (BaseException $ex) {
                    if (
                        false === $this->handleException($ex) ||
                        false === $this->finished
                    ) {
                        continue;
                    }

                    throw $ex;
                }
            }

            // Calling "dispatch:afterDispatch" event
            if (true === $hasEventsManager) {
                try {
                    $this->fireManagerEvent("dispatch:afterDispatch", $value);
                } catch (BaseException $ex) {
                    /**
                     * Still check for finished here as we want to prioritize
                     * `forwarding()` calls
                     */
                    if (
                        false === $this->handleException($ex) ||
                        false === $this->finished
                    ) {
                        continue;
                    }

                    throw $ex;
                }
            }
        }

        if (true === $hasEventsManager) {
            try {
                // Calling "dispatch:afterDispatchLoop" event
                // Note: We don't worry about forwarding in after dispatch loop.
                $this->fireManagerEvent("dispatch:afterDispatchLoop");
            } catch (BaseException $ex) {
                // Exception occurred in afterDispatchLoop.
                if (false === $this->handleException($ex)) {
                    return false;
                }

                // Otherwise, bubble Exception
                throw $ex;
            }
        }

        return $handler;
    }

    /**
     * Forwards the execution flow to another controller/action.
     *
     * ```php
     * $this->dispatcher->forward(
     *     [
     *         "controller" => "posts",
     *         "action"     => "index",
     *     ]
     * );
     * ```
     *
     * @param array $forward
     *
     * @return void
     * @throws DispatcherException
     */
    public function forward(array $forward): void
    {
        if (true === $this->isControllerInitialize) {
            /**
             * Note: Important that we do not throw a "throwDispatchException"
             * call here. This is important because it would allow the
             * application to break out of the defined logic inside the
             * dispatcher which handles all dispatch exceptions.
             */
            throw new DispatcherException(
                "Forwarding inside a controller's initialize() method is forbidden"
            );
        }

        /**
         * Save current values as previous to ensure calls to getPrevious
         * methods don't return null.
         */
        $this->previousNamespaceName = $this->namespaceName;
        $this->previousHandlerName   = $this->handlerName;
        $this->previousActionName    = $this->actionName;

        // Check if we need to forward to another namespace
        $this->namespaceName = $forward["namespace"] ?? $this->namespaceName;
        $this->handlerName   = $forward["controller"] ?? $this->handlerName;
        $this->handlerName   = $forward["task"] ?? $this->handlerName;
        $this->actionName    = $forward["action"] ?? $this->actionName;
        $this->parameters    = $forward["params"] ?? $this->parameters;

        $this->finished  = false;
        $this->forwarded = true;
    }

    /**
     * Gets the latest dispatched action name
     *
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * Gets the default action suffix
     *
     * @return string
     */
    public function getActionSuffix(): string
    {
        return $this->actionSuffix;
    }

    /**
     * Returns the current method to be/executed in the dispatcher
     *
     * @return string
     */
    public function getActiveMethod(): string
    {
        if (!isset($this->activeMethodMap[$this->actionName])) {
            $this->activeMethodMap[$this->actionName] = lcfirst(
                $this->toCamelCase($this->actionName)
            );
        }

        return $this->activeMethodMap[$this->actionName] . $this->actionSuffix;
    }

    /**
     * Returns bound models from binder instance
     *
     * ```php
     * class UserController extends Controller
     * {
     *     public function showAction(User $user)
     *     {
     *         // return array with $user
     *         $boundModels = $this->dispatcher->getBoundModels();
     *     }
     * }
     * ```
     *
     * @return array
     */
    public function getBoundModels(): array
    {
        if (null === $this->modelBinder) {
            return [];
        }

        return $this->modelBinder->getBoundModels();
    }

    /**
     * Returns the default namespace
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    /**
     * Possible class name that will be located to dispatch the request
     */
    public function getHandlerClass(): string
    {
        $this->resolveEmptyProperties();

        $handlerSuffix = $this->handlerSuffix;
        $handlerName   = $this->handlerName;
        $namespaceName = $this->namespaceName;

        // We don't camelize the classes if they are in namespaces
        if (true !== str_contains($handlerName, "\\")) {
            $camelizedClass = $this->toCamelCase($handlerName);
        } else {
            $camelizedClass = $handlerName;
        }

        // Create the complete controller class name prepending the namespace
        if ($namespaceName) {
            if (true !== str_ends_with($namespaceName, "\\")) {
                $namespaceName .= "\\";
            }

            $handlerClass = $namespaceName . $camelizedClass . $handlerSuffix;
        } else {
            $handlerClass = $camelizedClass . $handlerSuffix;
        }

        return $handlerClass;
    }

    /**
     * Gets the default handler suffix
     *
     * @return string
     */
    public function getHandlerSuffix(): string
    {
        return $this->handlerSuffix;
    }

    /**
     * Gets model binder
     *
     * @return BinderInterface|null
     */
    public function getModelBinder(): BinderInterface | null
    {
        return $this->modelBinder;
    }

    /**
     * Gets the module where the controller class is
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * Gets a namespace to be prepended to the current handler name
     *
     * @return string
     */
    public function getNamespaceName(): string
    {
        return $this->namespaceName;
    }

    /**
     * Gets a param by its name or numeric index
     *
     * @param int|string   $parameter
     * @param array|string $filters
     * @param mixed|null   $defaultValue
     *
     * @return mixed
     * @throws CliDispatcherException
     * @todo deprecate this in the future
     */
    public function getParam(
        int | string $parameter,
        array | string $filters = [],
        mixed $defaultValue = null
    ): mixed {
        return $this->getParameter($parameter, $filters, $defaultValue);
    }

    /**
     * Gets a param by its name or numeric index
     *
     * @param int|string   $parameter
     * @param array|string $filters
     * @param mixed|null   $defaultValue
     *
     * @return mixed
     * @throws CliDispatcherException
     */
    public function getParameter(
        int | string $parameter,
        array | string $filters = [],
        mixed $defaultValue = null
    ): mixed {
        if (!isset($this->parameters[$parameter])) {
            return $defaultValue;
        }

        $paramValue = $this->parameters[$parameter];
        if (empty($filters)) {
            return $paramValue;
        }

        if (null === $this->container) {
            $this->throwDispatchException(
                "A dependency injection container is required to "
                . "access the 'filter' service",
                DispatcherException::EXCEPTION_NO_DI
            );
        }

        /** @var FilterInterface $filter */
        $filter = $this->container->getShared("filter");

        return $filter->sanitize($paramValue, $filters);
    }

    /**
     * Gets action params
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Gets action params
     *
     * @return array
     * @todo deprecate this in the future
     */
    public function getParams(): array
    {
        return $this->getParameters();
    }

    /**
     * Returns value returned by the latest dispatched action
     *
     * @return mixed|null
     */
    public function getReturnedValue(): mixed
    {
        return $this->returnedValue;
    }

    /**
     * Check if a param exists
     *
     * @param int|string $parameter
     *
     * @return bool
     * @todo deprecate this in the future
     */
    public function hasParam(int | string $parameter): bool
    {
        return $this->hasParameter($parameter);
    }

    /**
     * Check if a param exists
     *
     * @param int|string $parameter
     *
     * @return bool
     */
    public function hasParameter(int | string $parameter): bool
    {
        return isset($this->parameters[$parameter]);
    }

    /**
     * Checks if the dispatch loop is finished or has more pendent
     * controllers/tasks to dispatch
     *
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * Sets the action name to be dispatched
     *
     * @param string $actionName
     *
     * @return void
     */
    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * Sets the default action suffix
     *
     * @param string $actionSuffix
     *
     * @return void
     */
    public function setActionSuffix(string $actionSuffix): void
    {
        $this->actionSuffix = $actionSuffix;
    }

    /**
     * Sets the default action name
     *
     * @param string $actionName
     *
     * @return void
     */
    public function setDefaultAction(string $actionName): void
    {
        $this->defaultAction = $actionName;
    }

    /**
     * Sets the default namespace
     *
     * @param string $defaultNamespace
     *
     * @return void
     */
    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * Sets the default suffix for the handler
     *
     * @param string $handlerSuffix
     *
     * @return void
     */
    public function setHandlerSuffix(string $handlerSuffix): void
    {
        $this->handlerSuffix = $handlerSuffix;
    }

    /**
     * Enable model binding during dispatch
     *
     * ```php
     * $di->set(
     *     'dispatcher',
     *     function() {
     *         $dispatcher = new Dispatcher();
     *
     *         $dispatcher->setModelBinder(
     *             new Binder(),
     *             'cache'
     *         );
     *
     *         return $dispatcher;
     *     }
     * );
     * ```
     *
     * @param BinderInterface              $modelBinder
     * @param AdapterInterface|string|null $cache
     *
     * @return DispatcherInterface
     */
    public function setModelBinder(
        BinderInterface $modelBinder,
        AdapterInterface | string | null $cache = null
    ): DispatcherInterface {
        if (is_string($cache)) {
            $cache = $this->container->get($cache);
        }

        if ($cache instanceof AdapterInterface) {
            $modelBinder->setCache($cache);
        }

        $this->modelBinding = true;
        $this->modelBinder  = $modelBinder;

        return $this;
    }

    /**
     * Sets the module where the controller is (only informative)
     *
     * @param string $moduleName
     *
     * @return void
     */
    public function setModuleName(string $moduleName): void
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Sets the namespace where the controller class is
     *
     * @param string $namespaceName
     *
     * @return void
     */
    public function setNamespaceName(string $namespaceName): void
    {
        $this->namespaceName = $namespaceName;
    }

    /**
     * Set a param by its name or numeric index
     *
     * @param int|string $parameter
     * @param mixed      $value
     *
     * @return void
     * @todo deprecate this in the future
     */
    public function setParam(int | string $parameter, mixed $value): void
    {
        $this->setParameter($parameter, $value);
    }

    /**
     * Set a param by its name or numeric index
     *
     * @param int|string $parameter
     * @param mixed      $value
     *
     * @return void
     */
    public function setParameter(int | string $parameter, mixed $value): void
    {
        $this->parameters[$parameter] = $value;
    }

    /**
     * Sets action params to be dispatched
     *
     * @param array $parameters
     *
     * @return void
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets action params to be dispatched
     *
     * @param array $parameters
     *
     * @return void
     * @todo deprecate this in the future
     */
    public function setParams(array $parameters): void
    {
        $this->setParameters($parameters);
    }

    /**
     * Sets the latest returned value by an action manually
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setReturnedValue(mixed $value): void
    {
        $this->returnedValue = $value;
    }

    /**
     * Check if the current executed action was forwarded by another one
     *
     * @return bool
     */
    public function wasForwarded(): bool
    {
        return $this->forwarded;
    }

    /**
     * Handles a user exception
     *
     * @param BaseException $exception
     *
     * @return false|void
     */
    abstract protected function handleException(BaseException $exception);

    /**
     * Set empty properties to their defaults (where defaults are available)
     */
    protected function resolveEmptyProperties(): void
    {
        $this->namespaceName = !empty($this->namespaceName)
            ? $this->namespaceName
            : $this->defaultNamespace;
        $this->handlerName   = !empty($this->handlerName)
            ? $this->handlerName
            : $this->defaultHandler;
        $this->actionName    = !empty($this->actionName)
            ? $this->actionName
            : $this->defaultAction;
    }

    /**
     * Throws an internal exception
     *
     * @param string $message
     * @param int    $exceptionCode
     *
     * @return false
     * @throws CliDispatcherException
     */
    abstract protected function throwDispatchException(string $message, int $exceptionCode = 0);

    /**
     * @param string $input
     *
     * @return string
     */
    protected function toCamelCase(string $input): string
    {
        if (!isset($this->camelCaseMap[$input])) {
            $this->camelCaseMap[$input] = implode(
                "",
                array_map(
                    "ucfirst",
                    preg_split("/[_-]+/", $input)
                )
            );
        }

        return $this->camelCaseMap[$input];
    }
}
