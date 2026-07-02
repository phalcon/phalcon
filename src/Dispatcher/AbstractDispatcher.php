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
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Di\DiInterface;
use Phalcon\Dispatcher\Exception as PhalconException;
use Phalcon\Dispatcher\Exceptions\ForwardInInitializeForbidden;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
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
use function ucfirst;

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
 */
abstract class AbstractDispatcher extends AbstractInjectionAware implements DispatcherInterface, EventsAwareInterface
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
     * @var object|null
     */
    protected $activeHandler = null;

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
     * @var array
     */
    protected array $handlerHookCache = [];

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
    protected ?BinderInterface $modelBinder = null;

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
     * @var mixed|null
     */
    protected mixed $returnedValue = null;

    /**
     * @param mixed  $handler
     * @param string $actionMethod
     * @param array  $params
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

        if (
            null !== $this->eventsManager &&
            $this->eventsManager instanceof ManagerInterface
        ) {
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
             * loop's own `is_callable()` check ran against the original pair, so
             * re-validate the (possibly mutated) callable here. A substituted,
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

        $result = call_user_func_array(
            [$altHandler, $altAction],
            array_values($altParams)
        );

        if (
            null !== $this->eventsManager &&
            $this->eventsManager instanceof ManagerInterface
        ) {
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
     * @return mixed
     * @throws Exception
     */
    public function dispatch()
    {
        $container = $this->container;

        if (null === $container) {
            $this->throwDispatchException(
                "A dependency injection container is required to access related dispatching services",
                PhalconException::EXCEPTION_NO_DI
            );

            return false;
        }

        $eventsManager    = $this->eventsManager;
        $hasEventsManager = is_object($eventsManager);
        $this->finished   = true;

        if ($hasEventsManager) {
            try {
                if (
                    $eventsManager->fire("dispatch:beforeDispatchLoop", $this) === false &&
                    $this->finished !== false
                ) {
                    return false;
                }
            } catch (Exception $e) {
                $status = $this->handleException($e);

                if ($this->finished !== false) {
                    if ($status === false) {
                        return false;
                    }

                    throw $e;
                }
            }
        }

        $value            = null;
        $handler          = null;
        $numberDispatches = 0;
        $this->finished   = false;

        while (!$this->finished) {
            $numberDispatches++;

            if ($numberDispatches === 256) {
                $this->throwDispatchException(
                    "Dispatcher has detected a cyclic routing causing stability problems",
                    PhalconException::EXCEPTION_CYCLIC_ROUTING
                );

                break;
            }

            $this->finished = true;

            $this->resolveEmptyProperties();

            if ($hasEventsManager) {
                try {
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

            $hasService = (bool) $container->has($handlerClass);
            if (!$hasService) {
                $hasService = class_exists($handlerClass);
            }

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

            if (!is_array($this->params)) {
                $status = $this->throwDispatchException(
                    "Action parameters must be an Array",
                    PhalconException::EXCEPTION_INVALID_PARAMS
                );

                if ($status === false && $this->finished === false) {
                    continue;
                }

                break;
            }

            $actionMethod = $this->getActiveMethod();

            if (!is_callable([$handler, $actionMethod])) {
                if ($hasEventsManager) {
                    if ($eventsManager->fire("dispatch:beforeNotFoundAction", $this) === false) {
                        continue;
                    }

                    if ($this->finished === false) {
                        continue;
                    }
                }

                $status = $this->throwDispatchException(
                    "Action '" . $actionName . "' was not found on handler '" . $handlerName . "'",
                    PhalconException::EXCEPTION_ACTION_NOT_FOUND
                );

                if ($status === false && $this->finished === false) {
                    continue;
                }

                break;
            }

            if ($hasEventsManager) {
                try {
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

            if ($isNewHandler) {
                if ($hookCache[1]) {
                    try {
                        $this->isControllerInitialize = true;

                        $handler->initialize();
                    } catch (Exception $e) {
                        $this->isControllerInitialize = false;

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

                if (
                    !$hasEventsManager &&
                    null !== $this->eventsManager &&
                    $this->eventsManager instanceof ManagerInterface
                ) {
                    $eventsManager    = $this->eventsManager;
                    $hasEventsManager = true;
                }

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

            if ($this->modelBinding) {
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
            if ($hasEventsManager) {
                if ($eventsManager->fire("dispatch:afterBinding", $this) === false) {
                    continue;
                }

                if ($this->finished === false) {
                    continue;
                }
            }

            if ($hookCache[2]) {
                if ($handler->afterBinding($this) === false) {
                    continue;
                }

                if ($this->finished === false) {
                    continue;
                }
            }

            $this->lastHandler = $handler;

            try {
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

            if ($hasEventsManager) {
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

            if ($hasEventsManager) {
                try {
                    $eventsManager->fire("dispatch:afterDispatch", $this, $value);
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

        if ($hasEventsManager) {
            try {
                $eventsManager->fire("dispatch:afterDispatchLoop", $this);
            } catch (Exception $e) {
                if ($this->handleException($e) === false) {
                    return false;
                }

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
     *
     * @param array $forward
     *
     * @return void
     * @throws PhalconException
     */
    public function forward(array $forward): void
    {
        if ($this->isControllerInitialize === true) {
            throw new ForwardInInitializeForbidden();
        }

        $this->previousNamespaceName = $this->namespaceName;
        $this->previousHandlerName   = $this->handlerName;
        $this->previousActionName    = $this->actionName;

        if (isset($forward["namespace"])) {
            $this->namespaceName = $forward["namespace"];
        }

        if (isset($forward["controller"])) {
            $this->handlerName = $forward["controller"];
        } elseif (isset($forward["task"])) {
            $this->handlerName = $forward["task"];
        }

        if (isset($forward["action"])) {
            $this->actionName = $forward["action"];
        }

        if (isset($forward["params"])) {
            $this->params = $forward["params"];
        }

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
     *
     * @return string
     */
    public function getHandlerClass(): string
    {
        $this->resolveEmptyProperties();

        $handlerSuffix = $this->handlerSuffix;
        $handlerName   = $this->handlerName;
        $namespaceName = $this->namespaceName;

        if (!str_contains($handlerName, "\\")) {
            $camelizedClass = $this->toCamelCase($handlerName);
        } else {
            $camelizedClass = $handlerName;
        }

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
    public function getModelBinder(): ?BinderInterface
    {
        return $this->modelBinder;
    }

    /**
     * Gets the module where the controller class is
     *
     * @return string|null
     */
    public function getModuleName(): ?string
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
     * @param string|int        $param
     * @param string|array|null $filters
     * @param mixed             $defaultValue
     *
     * @return mixed
     * @deprecated Use getParameter() instead
     *
     * Note: The interface declares `getParam($param, $filters = null)` without
     * the `$defaultValue` argument, so code typed against `DispatcherInterface`
     * cannot use the default-value feature. This signature drift is intentional
     * for now; the interface and implementation will be aligned in the next
     * major version.
     */
    public function getParam(
        string | int $param,
        string | array | null $filters = null,
        mixed $defaultValue = null
    ): mixed {
        return $this->getParameter($param, $filters, $defaultValue);
    }

    /**
     * Gets a param by its name or numeric index
     *
     * @param string|int        $param
     * @param string|array|null $filters
     * @param mixed             $defaultValue
     *
     * @return mixed
     */
    public function getParameter(
        string | int $param,
        string | array | null $filters = null,
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
        return $this->params;
    }

    /**
     * Gets action params
     *
     * @return array
     * @deprecated Use getParameters() instead
     */
    public function getParams(): array
    {
        return $this->getParameters();
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
     * Gets previous dispatched handler name
     *
     * @return string
     */
    public function getPreviousHandlerName(): string
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
     * Returns value returned by the latest dispatched action
     *
     * @return mixed
     */
    public function getReturnedValue(): mixed
    {
        return $this->returnedValue;
    }

    /**
     * Check if a param exists
     *
     * @param string|int $param
     *
     * @return bool
     * @deprecated Use hasParameter() instead
     */
    public function hasParam(string | int $param): bool
    {
        return $this->hasParameter($param);
    }

    /**
     * Check if a param exists
     *
     * @param string|int $param
     *
     * @return bool
     */
    public function hasParameter(string | int $param): bool
    {
        return isset($this->params[$param]);
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
     * @param BinderInterface $modelBinder
     * @param mixed           $cache
     *
     * @return DispatcherInterface
     */
    public function setModelBinder(
        BinderInterface $modelBinder,
        mixed $cache = null
    ): DispatcherInterface {
        if (is_string($cache)) {
            $cache = $this->container->get($cache);
        }

        if (null !== $cache) {
            $modelBinder->setCache($cache);
        }

        $this->modelBinding = true;
        $this->modelBinder  = $modelBinder;

        return $this;
    }

    /**
     * Sets the module where the controller is (only informative)
     *
     * @param string|null $moduleName
     *
     * @return void
     */
    public function setModuleName(string | null $moduleName = null): void
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
     * @param string|int $param
     * @param mixed      $value
     *
     * @return void
     * @deprecated Use setParameter() instead
     */
    public function setParam(string | int $param, mixed $value): void
    {
        $this->setParameter($param, $value);
    }

    /**
     * Set a param by its name or numeric index
     *
     * @param string|int $param
     * @param mixed      $value
     *
     * @return void
     */
    public function setParameter(string | int $param, mixed $value): void
    {
        $this->params[$param] = $value;
    }

    /**
     * Sets action params to be dispatched
     *
     * @param array $params
     *
     * @return void
     */
    public function setParameters(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Sets action params to be dispatched
     *
     * @param array $params
     *
     * @return void
     * @deprecated Use setParameters() instead
     */
    public function setParams(array $params): void
    {
        $this->setParameters($params);
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
     * @param Exception $exception
     *
     * @return mixed
     */
    abstract protected function handleException(Exception $exception);

    /**
     * Set empty properties to their defaults (where defaults are available)
     *
     * @return void
     */
    protected function resolveEmptyProperties(): void
    {
        if (!$this->namespaceName) {
            $this->namespaceName = $this->defaultNamespace;
        }

        if (!$this->handlerName) {
            $this->handlerName = $this->defaultHandler;
        }

        if (!$this->actionName) {
            $this->actionName = $this->defaultAction;
        }
    }

    /**
     * Throws an internal exception
     *
     * @param string $message
     * @param int    $exceptionCode
     *
     * @return mixed
     */
    abstract protected function throwDispatchException(
        string $message,
        int $exceptionCode = 0
    );

    /**
     * @param string $input
     *
     * @return string
     */
    protected function toCamelCase(string $input): string
    {
        $camelCaseInput = $this->camelCaseMap[$input] ?? null;

        if (null === $camelCaseInput) {
            $camelCaseInput = implode(
                "",
                array_map(
                    "ucfirst",
                    preg_split("/[_-]+/", $input)
                )
            );

            $this->camelCaseMap[$input] = $camelCaseInput;
        }

        return $camelCaseInput;
    }
}
