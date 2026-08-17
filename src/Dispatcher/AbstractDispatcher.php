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

use Exception;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Contracts\Dispatcher\DispatcherTypes;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Di\DiInterface;
use Phalcon\Dispatcher\Exception as PhalconException;
use Phalcon\Dispatcher\Exceptions\ForwardInInitializeForbidden;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Filter\FilterInterface;
use Phalcon\Mvc\Model\Binder;
use Phalcon\Mvc\Model\BinderInterface;
use Phalcon\Support\Collection;

use function array_map;
use function array_values;
use function call_user_func_array;
use function class_exists;
use function implode;
use function is_callable;
use function is_object;
use function lcfirst;
use function method_exists;
use function preg_split;
use function spl_object_hash;
use function str_contains;
use function str_ends_with;

/**
 * This is the base class for Phalcon\Mvc\Dispatcher and Phalcon\Cli\Dispatcher.
 * This class can't be instantiated directly, you can use it to create your own
 * dispatchers.
 *
 * ## Error protocol
 *
 * Subclasses (including third-party ones) MUST implement the two abstract
 * error hooks {@see throwDispatchException()} and {@see handleException()}.
 * The dispatch loop calls them on every error/exception path; a subclass that
 * omits them cannot be loaded.
 *
 * ## Hook channels
 *
 * A single lifecycle point can be intercepted through three independent
 * channels. For any given point they run in this order:
 *
 * 1. **Events-manager listener** - e.g. `dispatch:beforeExecuteRoute`. A
 *    listener returning `false` cancels; calling `forward()` re-enters the
 *    loop; throwing routes through {@see handleException()}.
 * 2. **Duck-typed handler method** - e.g. a `beforeExecuteRoute()` method on
 *    the controller/task itself (presence is cached per class). Same
 *    `false` / `forward()` cancellation semantics as the event.
 * 3. **`dispatch:beforeCallAction` observer** - fired by
 *    {@see callActionMethod()} with a `Phalcon\Support\Collection` carrying
 *    the mutable keys `handler`, `action` and `params`. Listeners may rewrite
 *    those keys to change *what* gets invoked; the substituted callable is
 *    re-validated before the call. `dispatch:afterCallAction` receives the
 *    same Collection plus a `result` key.
 *
 * @phpstan-import-type dispatcher_bound_models from DispatcherTypes
 * @phpstan-import-type dispatcher_forward from DispatcherTypes
 * @phpstan-import-type dispatcher_handler_hashes from DispatcherTypes
 * @phpstan-import-type dispatcher_hook_cache from DispatcherTypes
 * @phpstan-import-type dispatcher_method_map from DispatcherTypes
 * @phpstan-import-type dispatcher_params from DispatcherTypes
 */
abstract class AbstractDispatcher extends AbstractInjectionAware implements DispatcherInterface, EventsAwareInterface
{
    use EventsAwareTrait;

    protected string $actionName = "";
    protected string $actionSuffix = "Action";

    /**
     * @var object|null
     */
    protected $activeHandler = null;
    /**
     * @phpstan-var dispatcher_method_map
     */
    protected array $activeMethodMap = [];
    /**
     * @phpstan-var dispatcher_method_map
     */
    protected array $camelCaseMap = [];
    protected string $defaultAction = "";
    protected string $defaultHandler = "";
    protected string $defaultNamespace = "";
    protected bool $finished = false;
    protected bool $forwarded = false;
    /**
     * @phpstan-var dispatcher_handler_hashes
     */
    protected array $handlerHashes = [];
    /**
     * @phpstan-var dispatcher_hook_cache
     */
    protected array $handlerHookCache = [];
    protected string $handlerName = "";
    protected string $handlerSuffix = "";
    protected bool $isControllerInitialize = false;
    /**
     * @var mixed
     */
    protected mixed $lastHandler = null;
    protected ?BinderInterface $modelBinder = null;
    protected bool $modelBinding = false;
    protected ?string $moduleName = "";
    protected string $namespaceName = "";
    /**
     * @phpstan-var dispatcher_params
     */
    protected array $params = [];
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
     * @todo fix the type in v7
     */
    protected mixed $returnedValue = null;

    /**
     * @param mixed  $handler
     * @param string $actionMethod
     *
     * @phpstan-param dispatcher_params $params
     *
     * @return mixed
     */
    public function callActionMethod(
        mixed $handler,
        string $actionMethod,
        array $params = []
    ): mixed {
        $altHandler = $handler;
        $altAction  = $actionMethod;
        $altParams  = $params;

        if (null !== $this->eventsManager) {
            $observer = new Collection([
                "handler" => $handler,
                "action"  => $actionMethod,
                "params"  => $params,
            ]);

            $this->eventsManager->fire(
                "dispatch:beforeCallAction",
                $this,
                $observer
            );

            $altHandler = $observer->get("handler");
            $altAction  = $observer->get("action");
            $altParams  = $observer->get("params", [], "array");

            /**
             * The `dispatch:beforeCallAction` observer may replace the handler
             * and/or the action (see the hook-channel notes on this class). The
             * loop's own `is_callable()` check ran against the *original* pair,
             * so re-validate the (possibly mutated) callable here. A substituted,
             * non-existent target then fails through the dispatcher's own
             * EXCEPTION_ACTION_NOT_FOUND channel instead of producing a raw
             * call_user_func_array() fatal.
             */
            if (!is_callable([$altHandler, $altAction])) {
                $this->throwDispatchException(
                    "Action '" . $this->actionName . "' was not found on handler '" . $this->handlerName . "'",
                    PhalconException::EXCEPTION_ACTION_NOT_FOUND
                );

                return false;
            }
        }

        /** @var callable $callable */
        $callable = [$altHandler, $altAction];
        /** @phpstan-var dispatcher_params $altParams */

        $result = call_user_func_array(
            $callable,
            array_values($altParams)
        );

        if (null !== $this->eventsManager) {
            $observer["result"] = $result;

            $this->eventsManager->fire(
                "dispatch:afterCallAction",
                $this,
                $observer
            );
        }

        return $result;
    }

    /**
     * Process the results of the router by calling into the appropriate
     * controller action(s) including any routing data or injected parameters.
     *
     * @return mixed Returns the dispatched handler class (the Controller for Mvc dispatching or a Task
     *               for CLI dispatching) or <tt>false</tt> if an exception occurred and the operation was
     *               stopped by returning <tt>false</tt> in the exception handler.
     *
     * @throws \Exception if any uncaught or unhandled exception occurs during the dispatcher process.
     */
    public function dispatch(): mixed
    {
        $container = $this->container;

        if (null === $container) {
            $this->throwDispatchException(
                "A dependency injection container is required to access related dispatching services",
                PhalconException::EXCEPTION_NO_DI
            );

            return false;
        }

        $eventsManager  = $this->eventsManager;
        $this->finished = true;

        if (null !== $eventsManager) {
            try {
                // Calling beforeDispatchLoop event
                // Note: Allow user to forward in the beforeDispatchLoop.
                if (
                    $eventsManager->fire("dispatch:beforeDispatchLoop", $this) === false &&
                    $this->finished !== false
                ) {
                    return false;
                }
            } catch (Exception $e) {
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

                $status = $this->handleException($e);

                if ($this->finished !== false) {
                    // No forwarding
                    if ($status === false) {
                        return false;
                    }

                    // Otherwise, bubble Exception
                    throw $e;
                }

                // Otherwise, user forwarded, continue
            }
        }

        $value            = null;
        $handler          = null;
        $numberDispatches = 0;
        $this->finished   = false;

        while (!$this->finished) {
            $numberDispatches++;

            // Throw an exception after 256 consecutive forwards
            if ($numberDispatches === 256) {
                $this->throwDispatchException(
                    "Dispatcher has detected a cyclic routing causing stability problems",
                    PhalconException::EXCEPTION_CYCLIC_ROUTING
                );

                break;
            }

            $this->finished = true;

            $this->resolveEmptyProperties();

            if (null !== $eventsManager) {
                try {
                    // Calling "dispatch:beforeDispatch" event
                    if (
                        $eventsManager->fire("dispatch:beforeDispatch", $this) === false ||
                        $this->finished === false
                    ) {
                        continue;
                    }
                } catch (Exception $e) {
                    if (
                        $this->handleException($e) === false ||
                        $this->finished === false
                    ) {
                        continue;
                    }

                    throw $e;
                }
            }

            $handlerClass = $this->getHandlerClass();

            /**
             * Handlers are retrieved as shared instances from the Service
             * Container
             */
            $hasService = (bool) $container->has($handlerClass);
            if (!$hasService) {
                /**
                 * DI does not have a service with that name, try to load it
                 * using an autoloader
                 */
                $hasService = class_exists($handlerClass);
            }

            // If the service can be loaded we throw an exception
            if (!$hasService) {
                $status = $this->throwDispatchException(
                    $handlerClass . " handler class cannot be loaded",
                    PhalconException::EXCEPTION_HANDLER_NOT_FOUND
                );

                if ($status === false && $this->finished === false) {
                    continue;
                }

                break;
            }

            $handler = $container->getShared($handlerClass);

            // Handlers must be only objects
            if (!is_object($handler)) {
                $status = $this->throwDispatchException(
                    "Invalid handler returned from the services container",
                    PhalconException::EXCEPTION_INVALID_HANDLER
                );

                if ($status === false && $this->finished === false) {
                    continue;
                }

                break;
            }

            // Check if the handler is new (hasn't been initialized).
            $handlerHash  = spl_object_hash($handler);
            $isNewHandler = !isset($this->handlerHashes[$handlerHash]);

            if ($isNewHandler) {
                $this->handlerHashes[$handlerHash] = true;
            }

            $this->activeHandler = $handler;

            if (!isset($this->handlerHookCache[$handlerClass])) {
                $this->handlerHookCache[$handlerClass] = [
                    method_exists($handler, "beforeExecuteRoute"),
                    method_exists($handler, "initialize"),
                    method_exists($handler, "afterBinding"),
                    method_exists($handler, "afterExecuteRoute"),
                ];
            }

            $hookCache = $this->handlerHookCache[$handlerClass];

            $namespaceName = $this->namespaceName;
            $handlerName   = $this->handlerName;
            $actionName    = $this->actionName;

            /**
             * Check if the params is an array
             */
            if (!is_array($this->params)) {
                /**
                 * An invalid parameter variable was passed throw an exception
                 */
                $status = $this->throwDispatchException(
                    "Action parameters must be an Array",
                    PhalconException::EXCEPTION_INVALID_PARAMS
                );

                if ($status === false && $this->finished === false) {
                    continue;
                }

                break;
            }

            // Check if the method exists in the handler
            $actionMethod = $this->getActiveMethod();

            if (!is_callable([$handler, $actionMethod])) {
                if (null !== $eventsManager) {
                    if ($eventsManager->fire("dispatch:beforeNotFoundAction", $this) === false) {
                        continue;
                    }

                    if ($this->finished === false) {
                        continue;
                    }
                }

                /**
                 * Try to throw an exception when an action isn't defined on the
                 * object
                 */
                $status = $this->throwDispatchException(
                    "Action '" . $actionName . "' was not found on handler '" . $handlerName . "'",
                    PhalconException::EXCEPTION_ACTION_NOT_FOUND
                );

                if ($status === false && $this->finished === false) {
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
            if (null !== $eventsManager) {
                try {
                    // Calling "dispatch:beforeExecuteRoute" event
                    if (
                        $eventsManager->fire("dispatch:beforeExecuteRoute", $this) === false ||
                        $this->finished === false
                    ) {
                        $container->remove($handlerClass);
                        continue;
                    }
                } catch (Exception $e) {
                    if (
                        $this->handleException($e) === false ||
                        $this->finished === false
                    ) {
                        $container->remove($handlerClass);
                        continue;
                    }

                    throw $e;
                }
            }

            if ($hookCache[0]) {
                try {
                    // Calling "beforeExecuteRoute" as direct method
                    if (
                        $handler->beforeExecuteRoute($this) === false ||
                        $this->finished === false
                    ) {
                        $container->remove($handlerClass);
                        continue;
                    }
                } catch (Exception $e) {
                    if (
                        $this->handleException($e) === false ||
                        $this->finished === false
                    ) {
                        $container->remove($handlerClass);
                        continue;
                    }

                    throw $e;
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
             * Note (historical): the `initialize()` call and the
             * `dispatch:afterInitialize` event ideally would run *before* the
             * `beforeExecuteRoute` event/method blocks. This was a bug in the
             * original design that could not be changed due to widespread
             * implementation. The reordering was once slated for 4.0 but never
             * shipped; it remains deferred to a future major version, where the
             * BC break is acceptable and the container-eviction workaround below
             * can be removed along with it.
             *
             * @see https://github.com/phalcon/cphalcon/pull/13112
             */
            if ($isNewHandler) {
                if ($hookCache[1]) {
                    try {
                        $this->isControllerInitialize = true;

                        $handler->initialize();
                    } catch (Exception $e) {
                        $this->isControllerInitialize = false;

                        /**
                         * If this is a dispatch exception (e.g. From
                         * forwarding) ensure we don't handle this twice. In
                         * order to ensure this does not happen all other
                         * exceptions thrown outside this method in this class
                         * should not call "throwDispatchException" but instead
                         * throw a normal Exception.
                         */
                        if (
                            $this->handleException($e) === false ||
                            $this->finished === false
                        ) {
                            continue;
                        }

                        throw $e;
                    }
                }

                $this->isControllerInitialize = false;

                /**
                 * Refresh in case initialize() attached an events manager to
                 * the dispatcher when none existed at dispatch() entry.
                 */
                if (
                    null === $eventsManager &&
                    null !== $this->eventsManager
                ) {
                    $eventsManager = $this->eventsManager;
                }

                /**
                 * Calling "dispatch:afterInitialize" event
                 */
                if ($eventsManager) {
                    try {
                        if (
                            $eventsManager->fire("dispatch:afterInitialize", $this) === false ||
                            $this->finished === false
                        ) {
                            continue;
                        }
                    } catch (Exception $e) {
                        if (
                            $this->handleException($e) === false ||
                            $this->finished === false
                        ) {
                            continue;
                        }

                        throw $e;
                    }
                }
            }

            if ($this->modelBinding && null !== $this->modelBinder) {
                $modelBinder  = $this->modelBinder;
                $bindCacheKey = "_PHMB_" . $handlerClass . "_" . $actionMethod;

                $this->params = $modelBinder->bindToHandler(
                    $handler,
                    $this->params,
                    $bindCacheKey,
                    $actionMethod
                );
            }

            /**
             * Calling afterBinding
             *
             * Note: Unlike every other lifecycle hook, the `afterBinding` event
             * and method blocks deliberately have no try/catch. Exceptions
             * raised here are intended to bypass `handleException()` (and the
             * `dispatch:beforeException` channel) and bubble straight up: at
             * this point binding has already mutated the parameters and the
             * action is about to run, so swallowing/forwarding from a binding
             * listener is intentionally not supported. The only honored signals
             * are returning `false` (cancel) and `forward()` (`finished` flips
             * to `false`). This asymmetry is by design, not an oversight.
             */
            if (null !== $eventsManager) {
                if ($eventsManager->fire("dispatch:afterBinding", $this) === false) {
                    continue;
                }

                /**
                 * Check if the user made a forward in the listener
                 */
                if ($this->finished === false) {
                    continue;
                }
            }

            /**
             * Calling afterBinding as callback and event
             */
            if ($hookCache[2]) {
                if ($handler->afterBinding($this) === false) {
                    continue;
                }

                /**
                 * Check if the user made a forward in the listener
                 */
                if ($this->finished === false) {
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
                    $this->params
                );

                if ($this->finished === false) {
                    continue;
                }
            } catch (Exception $e) {
                if (
                    $this->handleException($e) === false ||
                    $this->finished === false
                ) {
                    continue;
                }

                throw $e;
            }

            /**
             * Calling "dispatch:afterExecuteRoute" event
             */
            if (null !== $eventsManager) {
                try {
                    if (
                        $eventsManager->fire("dispatch:afterExecuteRoute", $this, $value) === false ||
                        $this->finished === false
                    ) {
                        continue;
                    }
                } catch (Exception $e) {
                    if (
                        $this->handleException($e) === false ||
                        $this->finished === false
                    ) {
                        continue;
                    }

                    throw $e;
                }
            }

            /**
             * Calling "afterExecuteRoute" as direct method
             */
            if ($hookCache[3]) {
                try {
                    if (
                        $handler->afterExecuteRoute($this, $value) === false ||
                        $this->finished === false
                    ) {
                        continue;
                    }
                } catch (Exception $e) {
                    if (
                        $this->handleException($e) === false ||
                        $this->finished === false
                    ) {
                        continue;
                    }

                    throw $e;
                }
            }

            // Calling "dispatch:afterDispatch" event
            if (null !== $eventsManager) {
                try {
                    $eventsManager->fire("dispatch:afterDispatch", $this, $value);
                } catch (Exception $e) {
                    /**
                     * Still check for finished here as we want to prioritize
                     * `forwarding()` calls
                     */
                    if (
                        $this->handleException($e) === false ||
                        $this->finished === false
                    ) {
                        continue;
                    }

                    throw $e;
                }
            }
        }

        if (null !== $eventsManager) {
            try {
                // Calling "dispatch:afterDispatchLoop" event
                // Note: We don't worry about forwarding in after dispatch loop.
                $eventsManager->fire("dispatch:afterDispatchLoop", $this);
            } catch (Exception $e) {
                // Exception occurred in afterDispatchLoop.
                if ($this->handleException($e) === false) {
                    return false;
                }

                // Otherwise, bubble Exception
                throw $e;
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
     */
    public function forward(array $forward): void
    {
        if ($this->isControllerInitialize === true) {
            /**
             * Note: Important that we do not throw a "throwDispatchException"
             * call here. This is important because it would allow the
             * application to break out of the defined logic inside the
             * dispatcher which handles all dispatch exceptions.
             */
            throw new ForwardInInitializeForbidden();
        }

        /**
         * Save current values as previous to ensure calls to getPrevious
         * methods don't return null.
         */
        $this->previousNamespaceName = $this->namespaceName;
        $this->previousHandlerName   = $this->handlerName;
        $this->previousActionName    = $this->actionName;

        // Check if we need to forward to another namespace
        if (isset($forward["namespace"])) {
            $this->namespaceName = $forward["namespace"];
        }

        // Check if we need to forward to another controller.
        if (isset($forward["controller"])) {
            $this->handlerName = $forward["controller"];
        } elseif (isset($forward["task"])) {
            $this->handlerName = $forward["task"];
        }

        // Check if we need to forward to another action
        if (isset($forward["action"])) {
            $this->actionName = $forward["action"];
        }

        // Check if we need to forward changing the current parameters
        if (isset($forward["params"])) {
            $this->params = $forward["params"];
        }

        $this->finished  = false;
        $this->forwarded = true;
    }

    /**
     * Gets the latest dispatched action name
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * Gets the default action suffix
     */
    public function getActionSuffix(): string
    {
        return $this->actionSuffix;
    }

    /**
     * Returns the current method to be/executed in the dispatcher
     */
    public function getActiveMethod(): string
    {
        $activeMethodName = $this->activeMethodMap[$this->actionName] ?? null;

        if (null === $activeMethodName) {
            $activeMethodName = lcfirst(
                $this->toCamelCase($this->actionName)
            );

            $this->activeMethodMap[$this->actionName] = $activeMethodName;
        }

        return $activeMethodName . $this->actionSuffix;
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
     * @phpstan-return dispatcher_bound_models
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
        if (!str_contains($handlerName, "\\")) {
            $camelizedClass = $this->toCamelCase($handlerName);
        } else {
            $camelizedClass = $handlerName;
        }

        // Create the complete controller class name prepending the namespace
        if ($namespaceName) {
            if (!str_ends_with($namespaceName, "\\")) {
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
     */
    public function getHandlerSuffix(): string
    {
        return $this->handlerSuffix;
    }

    /**
     * Gets model binder
     */
    public function getModelBinder(): ?BinderInterface
    {
        return $this->modelBinder;
    }

    /**
     * Gets the module where the controller class is
     */
    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }

    /**
     * Gets a namespace to be prepended to the current handler name
     */
    public function getNamespaceName(): string
    {
        return $this->namespaceName;
    }

    /**
     * Gets a param by its name or numeric index
     *
     * @phpstan-param array-key $param
     * @phpstan-param mixed $filters
     * @phpstan-param mixed $defaultValue
     *
     * @deprecated Use getParameter() instead
     *
     * Note: The interface declares `getParam(param, filters = null)` without the
     * `defaultValue` argument, so code typed against `DispatcherInterface`
     * cannot use the default-value feature. This signature drift is intentional
     * for now; the interface and implementation will be aligned in the next
     * major version.
     */
    public function getParam(
        mixed $param,
        mixed $filters = null,
        mixed $defaultValue = null
    ): mixed {
        return $this->getParameter($param, $filters, $defaultValue);
    }

    /**
     * Gets a param by its name or numeric index
     *
     * @phpstan-param array-key $param
     * @phpstan-param mixed $filters
     * @phpstan-param mixed $defaultValue
     */
    public function getParameter(
        mixed $param,
        mixed $filters = null,
        mixed $defaultValue = null
    ): mixed {
        if (!isset($this->params[$param])) {
            return $defaultValue;
        }

        $paramValue = $this->params[$param];

        if (null === $filters) {
            return $paramValue;
        }

        if (null === $this->container) {
            $this->throwDispatchException(
                "A dependency injection container is required to access the 'filter' service",
                PhalconException::EXCEPTION_NO_DI
            );
        }

        /** @var DiInterface $container */
        $container = $this->container;
        /** @var FilterInterface $filter */
        $filter = $container->getShared("filter");
        /** @var array<array-key, mixed>|string $filters */

        return $filter->sanitize($paramValue, $filters);
    }

    /**
     * Gets action params
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * Gets action params
     *
     * @deprecated Use getParameters() instead
     */
    public function getParams(): array
    {
        return $this->getParameters();
    }

    /**
     * Gets previous dispatched action name
     */
    public function getPreviousActionName(): string
    {
        return $this->previousActionName;
    }

    /**
     * Gets previous dispatched handler name
     */
    public function getPreviousHandlerName(): string
    {
        return $this->previousHandlerName;
    }

    /**
     * Gets previous dispatched namespace name
     */
    public function getPreviousNamespaceName(): string
    {
        return $this->previousNamespaceName;
    }

    /**
     * Returns value returned by the latest dispatched action
     */
    public function getReturnedValue(): mixed
    {
        return $this->returnedValue;
    }

    /**
     * Check if a param exists
     *
     * @phpstan-param array-key $param
     *
     * @deprecated Use hasParameter() instead
     */
    public function hasParam(mixed $param): bool
    {
        return $this->hasParameter($param);
    }

    /**
     * Check if a param exists
     *
     * @phpstan-param array-key $param
     */
    public function hasParameter(mixed $param): bool
    {
        return isset($this->params[$param]);
    }

    /**
     * Checks if the dispatch loop is finished or has more pendent
     * controllers/tasks to dispatch
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * Sets the action name to be dispatched
     */
    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }

    /**
     * Sets the default action suffix
     */
    public function setActionSuffix(string $actionSuffix): void
    {
        $this->actionSuffix = $actionSuffix;
    }

    /**
     * Sets the default action name
     */
    public function setDefaultAction(string $actionName): void
    {
        $this->defaultAction = $actionName;
    }

    /**
     * Sets the default namespace
     */
    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }


    /**
     * Sets the default suffix for the handler
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
     */
    public function setModelBinder(
        BinderInterface $modelBinder,
        mixed $cache = null
    ): DispatcherInterface {
        if (is_string($cache)) {
            /** @var DiInterface $container */
            $container = $this->container;
            $cache     = $container->get($cache);
        }

        if (null !== $cache) {
            /** @var AdapterInterface $cache */
            $modelBinder->setCache($cache);
        }

        $this->modelBinding = true;
        $this->modelBinder  = $modelBinder;

        return $this;
    }

    /**
     * Sets the module where the controller is (only informative)
     */
    public function setModuleName(string | null $moduleName = null): void
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Sets the namespace where the controller class is
     */
    public function setNamespaceName(string $namespaceName): void
    {
        $this->namespaceName = $namespaceName;
    }

    /**
     * Set a param by its name or numeric index
     *
     * @deprecated Use setParameter() instead
     */
    public function setParam(mixed $param, mixed $value): void
    {
        $this->setParameter($param, $value);
    }

    /**
     * Set a param by its name or numeric index
     *
     * @phpstan-param array-key $param
     */
    public function setParameter(mixed $param, mixed $value): void
    {
        $this->params[$param] = $value;
    }

    /**
     * Sets action params to be dispatched
     *
     * @phpstan-param dispatcher_params $params
     */
    public function setParameters(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Sets action params to be dispatched
     *
     * @deprecated Use setParameters() instead
     */
    public function setParams(array $params): void
    {
        $this->setParameters($params);
    }

    /**
     * Sets the latest returned value by an action manually
     */
    public function setReturnedValue(mixed $value): void
    {
        $this->returnedValue = $value;
    }

    /**
     * Check if the current executed action was forwarded by another one
     */
    public function wasForwarded(): bool
    {
        return $this->forwarded;
    }

    /**
     * Handles a user exception triggered inside the dispatch loop.
     *
     * Subclasses implement the namespace-specific behavior (typically firing
     * the `dispatch:beforeException` event so listeners may forward or swallow
     * the exception).
     *
     * @param \Exception $exception
     *
     * @return mixed Return `false` to signal that the exception was handled
     *               (swallowed) and the current loop iteration should stop.
     *               Any other return value (including null) lets the caller
     *               bubble the exception, unless a forward was requested
     *               (`finished === false`).
     */
    abstract protected function handleException(Exception $exception);

    /**
     * Set empty properties to their defaults (where defaults are available)
     */
    protected function resolveEmptyProperties(): void
    {
        // If the current namespace is null we use the default namespace
        if (!$this->namespaceName) {
            $this->namespaceName = $this->defaultNamespace;
        }

        // If the handler is null we use the default handler
        if (!$this->handlerName) {
            $this->handlerName = $this->defaultHandler;
        }

        // If the action is null we use the default action
        if (!$this->actionName) {
            $this->actionName = $this->defaultAction;
        }
    }

    /**
     * Throws an internal dispatch exception.
     *
     * Subclasses build the namespace-specific exception and route it through
     * handleException() before throwing it when it was not handled.
     *
     * @param string $message
     * @param int    $exceptionCode
     *
     * @return mixed Returns `false` when handleException() swallowed the
     *               exception; otherwise the method throws and does not return.
     */
    abstract protected function throwDispatchException(
        string $message,
        int $exceptionCode = 0
    );

    protected function toCamelCase(string $input): string
    {
        $camelCaseInput = $this->camelCaseMap[$input] ?? null;

        if (null === $camelCaseInput) {
            $camelCaseInput = implode(
                "",
                array_map(
                    "ucfirst",
                    preg_split("/[_-]+/", $input) ?: []
                )
            );

            $this->camelCaseMap[$input] = $camelCaseInput;
        }

        return $camelCaseInput;
    }
}
