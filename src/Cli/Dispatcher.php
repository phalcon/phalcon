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

use Exception;
use Phalcon\Cli\Dispatcher\Exception as DispatcherException;
use Phalcon\Dispatcher\AbstractDispatcher as CliDispatcher;
use Phalcon\Filter\Exception as FilterException;

use function array_merge;
use function array_values;
use function call_user_func_array;

/**
 * Dispatching is the process of taking the command-line arguments, extracting
 * the module name, task name, action name, and optional parameters contained in
 * it, and then instantiating a task and calling an action on it.
 *
 * ```php
 * use Phalcon\Di\Di;
 * use Phalcon\Cli\Dispatcher;
 *
 * $di = new Di();
 *
 * $dispatcher = new Dispatcher();
 *
 * $dispatcher->setDi($di);
 *
 * $dispatcher->setTaskName("posts");
 * $dispatcher->setActionName("index");
 * $dispatcher->setParams([]);
 *
 * $handle = $dispatcher->dispatch();
 * ```
 */
class Dispatcher extends CliDispatcher implements DispatcherInterface
{
    protected string $defaultAction = "main";
    protected string $defaultHandler = "main";
    protected string $handlerSuffix = "Task";
    protected array $options = [];

    /**
     * Calls the action method.
     *
     * The CLI options collected by the dispatcher are appended to the
     * positional `parameters` before the call, so a task action receives any
     * options as trailing arguments after its declared parameters.
     */
    public function callActionMethod(
        mixed $handler,
        string $actionMethod,
        array $parameters = []
    ): mixed {
        // This is to make sure that the parameters are zero-indexed and
        // their order isn't overridden by any options when we merge the array.
        $params = array_values($parameters);
        $params = array_merge($params, $this->options);

        return call_user_func_array(
            [$handler, $actionMethod],
            $params
        );
    }

    /**
     * Returns the active task in the dispatcher
     */
    public function getActiveTask(): TaskInterface
    {
        return $this->activeHandler;
    }

    /**
     * Returns the latest dispatched controller
     */
    public function getLastTask(): TaskInterface
    {
        return $this->lastHandler;
    }

    /**
     * Gets an option by its name or numeric index
     *
     * @param int|string   $option
     * @param array|string $filters
     * @param mixed        $defaultValue
     */
    public function getOption(
        mixed $option,
        mixed $filters = null,
        mixed $defaultValue = null
    ): mixed {
        if (!isset($this->options[$option])) {
            return $defaultValue;
        }

        $optionValue = $this->options[$option];
        if (null === $filters) {
            return $optionValue;
        }

        $this->checkContainer(
            Exception::class,
            "the 'filter' service",
            DispatcherException::EXCEPTION_NO_DI
        );

        $filter = $this->container->getShared("filter");

        return $filter->sanitize($optionValue, $filters);
    }

    /**
     * Get dispatched options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Gets last dispatched task name
     */
    public function getTaskName(): string
    {
        return $this->handlerName;
    }

    /**
     * Gets the default task suffix
     */
    public function getTaskSuffix(): string
    {
        return $this->handlerSuffix;
    }

    /**
     * Check if an option exists
     */
    public function hasOption(mixed $option): bool
    {
        return isset($this->options[$option]);
    }

    /**
     * Sets the default task name
     */
    public function setDefaultTask(string $taskName): void
    {
        $this->defaultHandler = $taskName;
    }

    /**
     * Set the options to be dispatched
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * Sets the task name to be dispatched
     */
    public function setTaskName(string $taskName): void
    {
        $this->handlerName = $taskName;
    }

    /**
     * Sets the default task suffix
     */
    public function setTaskSuffix(string $taskSuffix): void
    {
        $this->handlerSuffix = $taskSuffix;
    }

    /**
     * Handles a user exception
     */
    protected function handleException(Exception $exception)
    {
        if (false === $this->fireManagerEvent("dispatch:beforeException", $exception)) {
            return false;
        }
    }

    /**
     * Throws an internal exception
     */
    protected function throwDispatchException(string $message, int $exceptionCode = 0)
    {
        $exception = new DispatcherException($message, $exceptionCode);

        if (false === $this->handleException($exception)) {
            return false;
        }

        throw $exception;
    }
}
