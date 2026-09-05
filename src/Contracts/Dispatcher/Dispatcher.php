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

namespace Phalcon\Contracts\Dispatcher;

/**
 * Canonical contract for Phalcon\Dispatcher\AbstractDispatcher.
 *
 * Note: The deprecated `getParam()`/`getParams()`/`hasParam()`/`setParam()`/
 * `setParams()` spellings are still declared for backwards compatibility and
 * are scheduled to be removed in the next major version in favor of their
 * `*Parameter` counterparts.
 *
 * @phpstan-import-type dispatcher_forward from DispatcherTypes
 * @phpstan-import-type dispatcher_params from DispatcherTypes
 */
interface Dispatcher
{
    /**
     * Dispatches a handle action taking into account the routing parameters
     *
     * @return bool|mixed
     */
    public function dispatch();

    /**
     * Forwards the execution flow to another controller/action
     *
     * @phpstan-param dispatcher_forward $forward
     */
    public function forward(array $forward): void;

    /**
     * Gets last dispatched action name
     */
    public function getActionName(): string;

    /**
     * Gets the default action suffix
     */
    public function getActionSuffix(): string;

    /**
     * Gets the default handler suffix
     */
    public function getHandlerSuffix(): string;

    /**
     * Gets a param by its name or numeric index
     *
     * @phpstan-param array-key $param
     * @phpstan-param mixed $filters
     *
     * @deprecated Use getParameter() instead
     *
     * Note: This signature omits the `$defaultValue` argument the
     * implementation accepts; the two will be aligned in the next major
     * version.
     */
    public function getParam(mixed $param, mixed $filters = null): mixed;

    /**
     * Gets a param by its name or numeric index
     *
     * @phpstan-param array-key $param
     * @phpstan-param mixed $filters
     */
    public function getParameter(mixed $param, mixed $filters = null): mixed;

    /**
     * Gets action params
     *
     * @phpstan-return dispatcher_params
     */
    public function getParameters(): array;

    /**
     * Gets action params
     *
     * @deprecated Use getParameters() instead
     *
     * @phpstan-return dispatcher_params
     */
    public function getParams(): array;

    /**
     * Returns value returned by the latest dispatched action
     */
    public function getReturnedValue(): mixed;

    /**
     * Check if a param exists
     *
     * @phpstan-param array-key $param
     *
     * @deprecated Use hasParameter() instead
     */
    public function hasParam(mixed $param): bool;

    /**
     * Checks if the dispatch loop is finished or has more pendent
     * controllers/tasks to dispatch
     */
    public function isFinished(): bool;

    /**
     * Sets the action name to be dispatched
     */
    public function setActionName(string $actionName): void;

    /**
     * Sets the default action suffix
     */
    public function setActionSuffix(string $actionSuffix): void;

    /**
     * Sets the default action name
     */
    public function setDefaultAction(string $actionName): void;

    /**
     * Sets the default namespace
     */
    public function setDefaultNamespace(string $defaultNamespace): void;

    /**
     * Sets the default suffix for the handler
     */
    public function setHandlerSuffix(string $handlerSuffix): void;

    /**
     * Sets the module name which the application belongs to
     *
     * @param string $moduleName
     */
    public function setModuleName(string | null $moduleName = null): void;

    /**
     * Sets the namespace which the controller belongs to
     */
    public function setNamespaceName(string $namespaceName): void;

    /**
     * Set a param by its name or numeric index
     *
     * @phpstan-param array-key $param
     *
     * @deprecated Use setParameter() instead
     */
    public function setParam(mixed $param, mixed $value): void;

    /**
     * Sets action params to be dispatched
     *
     * @phpstan-param dispatcher_params $params
     *
     * @deprecated Use setParameters() instead
     */
    public function setParams(array $params): void;
}
