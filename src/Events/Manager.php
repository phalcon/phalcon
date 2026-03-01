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

namespace Phalcon\Events;

use Closure;
use Phalcon\Db\Event\AbstractModelEvent;
use Phalcon\Db\Event\ModelEventNameEnum;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use SplPriorityQueue;

use function is_callable;
use function is_object;
use function method_exists;

/**
 * Phalcon Events Manager, offers an easy way to intercept and manipulate, if
 * needed, the normal flow of operation. With the EventsManager the developer
 * can create hooks or plugins that will offer monitoring of data, manipulation,
 * conditional execution and much more.
 */
class Manager implements ManagerInterface, EventDispatcherInterface
{
    /**
     * @var bool
     */
    protected bool $collect = false;

    /**
     * @var bool
     */
    protected bool $enablePriorities = false;

    /**
     * @var array
     */
    protected array $events = [];

    /**
     * @var array<array-key, mixed>
     */
    protected array $responses = [];

    /**
     * Returns if priorities are enabled
     *
     * @return bool
     */
    public function arePrioritiesEnabled(): bool
    {
        return $this->enablePriorities;
    }

    /**
     * Attach a listener to the events manager
     *
     * @param string|string[] $eventType
     * @param object|callable $handler
     * @param int             $priority
     */
    public function attach(
        string|array $eventType,
        callable | object $handler,
        int $priority = self::DEFAULT_PRIORITY
    ): void {

        if (is_array($eventType)) {
            $eventType = join(':', $eventType);
        }

        /** @var SplPriorityQueue|null $priorityQueue */
        $priorityQueue = $this->events[$eventType] ?? null;
        if (null === $priorityQueue) {
            // Create a SplPriorityQueue to store the events with priorities
            $priorityQueue = new SplPriorityQueue();

            // Set extraction flags to extract only the Data
            $priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);

            // Append the events to the queue
            $this->events[$eventType] = $priorityQueue;
        }

        if (true !== $this->enablePriorities) {
            $priority = self::DEFAULT_PRIORITY;
        }

        // Insert the handler in the queue
        $priorityQueue->insert($handler, $priority);
    }

    /**
     * Tells the event manager if it needs to collect all the responses returned
     * by every registered listener in a single fire
     *
     * @param bool $collect
     */
    public function collectResponses(bool $collect): void
    {
        $this->collect = $collect;
    }

    /**
     * Detach the listener from the events manager
     *
     * @param string          $eventType
     * @param object|callable $handler
     *
     * @return void
     * @throws Exception
     */
    public function detach(string $eventType, object | callable $handler): void
    {
        /** @var SplPriorityQueue|null $priorityQueue */
        $priorityQueue = $this->events[$eventType] ?? null;
        if (null !== $priorityQueue) {
            /**
             * SplPriorityQueue doesn't have a method for element deletion so we
             * need to rebuild the queue
             */
            $newPriorityQueue = new SplPriorityQueue();

            $newPriorityQueue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
            $priorityQueue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
            $priorityQueue->top();

            while ($priorityQueue->valid()) {
                $data = $priorityQueue->current();
                $priorityQueue->next();

                if ($handler !== $data['data']) {
                    $newPriorityQueue->insert($data['data'], $data['priority']);
                }
            }

            $this->events[$eventType] = $newPriorityQueue;
        }
    }

    /**
     * Removes all events from the EventsManager
     *
     * @param string|null $type
     */
    public function detachAll(string | null $type = null): void
    {
        $this->processDetachAllNullType($type);
        $this->processDetachAllNotNullType($type);
    }

    /**
     * Set if priorities are enabled in the EventsManager.
     *
     * A priority queue of events is a data structure similar
     * to a regular queue of events: we can also put and extract
     * elements from it. The difference is that each element in a
     * priority queue is associated with a value called priority.
     * This value is used to order elements of a queue: elements
     * with higher priority are retrieved before the elements with
     * lower priority.
     *
     * @param bool $enablePriorities
     */
    public function enablePriorities(bool $enablePriorities): void
    {
        $this->enablePriorities = $enablePriorities;
    }

    /**
     * Fires an event in the events manager causing the active listeners to be
     * notified about it
     *
     *```php
     * $eventsManager->fire("db", $connection);
     *```
     * @deprecated Please use PSR-14 EventDispatcherInterface (dispatch method with object)
     * @param string $eventType
     * @param object $source
     * @param mixed|null $data
     * @param bool $cancelable
     * @return mixed
     */
    public function fire(
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true,
    ): mixed {
        if (empty($this->events)) {
            return null;
        }

        // All valid events must have a colon separator
        if (!str_contains($eventType, ':')) {
            if (is_object($data)) {
                return $this->dispatch($data, $eventType, $source);
            }
            throw new Exception('Invalid event type ' . $eventType);
        }

        $eventParts = explode(':', $eventType);
        $type       = $eventParts[0];
        $eventName  = $eventParts[1];
        $status     = null;

        // Responses must be traced?
        if (true === $this->collect) {
            $this->responses = [];
        }

        // Create the event context
        $event = new Event($eventName, $source, $data, $cancelable);

        // Check if events are grouped by type
        $fireEvents = $this->events[$type] ?? null;
        if (is_object($fireEvents)) {
            // Call the events queue
            $status = $this->fireQueue($fireEvents, $event);
        }

        // Check if there are listeners for the event type itself
        $fireEvents = $this->events[$eventType] ?? null;
        if (is_object($fireEvents)) {
            // Call the events queue
            $status = $this->fireQueue($fireEvents, $event);
        }

        return $status;
    }

    /**
     * Dispatches an event to the appropriate event listeners.
     *
     * @param object $event The event object to be dispatched.
     * @param string|string[]|null $name The optional event name to look for.
     * @param object|null $source The optional source object of the event.
     *
     * @return mixed The result of the event listeners' processing or null if no listeners are found.
     */
    public function dispatch(object $event, string|array|null $name = null, ?object $source = null): mixed
    {
        if (empty($this->events)) {
            return null;
        }

        if (is_array($name)) {
            $name = join(':', $name);
        }

        if (!empty($this->events[$name])) {
            return $this->fireQueue($this->events[$name], $event);
        }

        $eventClassName = $event::class;
        if (!empty($this->events[$eventClassName])) {
            return $this->fireQueue($this->events[$eventClassName], $event);
        }

        return null;
    }

    /**
     * Internal handler to call a queue of events
     *
     * @param SplPriorityQueue $queue
     * @param object $event
     *
     * @return mixed
     */
    final protected function fireQueue(
        SplPriorityQueue $queue,
        object $event,
    ): mixed {
        $status = null;

        $cancelable = true;
        if ($event instanceof EventInterface) {
            // Tell if the event is cancelable
            $cancelable = $event->isCancelable();
        }

        // Responses need to be traced?
        $collected = $this->collect;

        // We need to clone the queue before iterate over it
        $iterator = clone $queue;

        // Move the queue to the top
        $iterator->top();

        while ($iterator->valid()) {
            // Get the current data
            $handler = $iterator->current();

            $iterator->next();

            // Only handler objects are valid
            if (true === $this->isValidHandler($handler)) {
                $status = $this->checkFireHandlerClosure($status, $handler, $event);
                $status = $this->checkFireHandlerMethod($status, $handler, $event);

                // Trace the response
                if (true === $collected) {
                    $this->responses[] = $status;
                }

                // Check if the event was stopped by the user
                if (true === $cancelable && $event instanceof EventInterface &&  true === $event->isStopped()) {
                    break;
                }

                if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * Returns all the attached listeners of a certain type
     *
     * @param string $type
     *
     * @return array<array-key, mixed>
     */
    public function getListeners(string $type): array
    {
        $listeners  = [];
        $fireEvents = $this->events[$type] ?? null;
        if (null !== $fireEvents) {
            $priorityQueue = clone $fireEvents;
            $priorityQueue->top();

            while ($priorityQueue->valid()) {
                $listeners[] = $priorityQueue->current();
                $priorityQueue->next();
            }
        }

        return $listeners;
    }

    /**
     * Returns all the responses returned by every handler executed by the last
     * 'fire' executed
     *
     * @return array<array-key, mixed>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * Check whether certain type of event has listeners
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasListeners(string $type): bool
    {
        return isset($this->events[$type]);
    }

    /**
     * Check if the events manager is collecting all all the responses returned
     * by every registered listener in a single fire
     *
     * @return bool
     */
    public function isCollecting(): bool
    {
        return $this->collect;
    }

    /**
     * @param mixed $handler
     *
     * @return bool
     */
    public function isValidHandler(mixed $handler): bool
    {
        if (!is_object($handler) && !is_callable($handler)) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $status
     * @param mixed $handler
     * @param object $event
     *
     * @return false|mixed
     */
    private function checkFireHandlerClosure(
        mixed $status,
        mixed $handler,
        object $event,
    ): mixed {
        // Check if the event is a closure
        if ($handler instanceof Closure || is_callable($handler)) {
            if ($event instanceof EventInterface) {
                $status = $handler($event, $event->getSource(), $event->getData());
            } else {
                $status = $handler($event);
            }
        }

        return $status;
    }

    /**
     * @param mixed $status
     * @param mixed $handler
     * @param object $event
     *
     * @return mixed
     */
    private function checkFireHandlerMethod(
        mixed $status,
        mixed $handler,
        object $event,
    ): mixed {
        // Check if the listener has implemented an event with the same name
        if (
            true !== ($handler instanceof Closure || is_callable($handler))
        ) {
            if (
                ($event instanceof EventInterface) &&
                ($eventName = $event->getType()) &&
                method_exists($handler, $eventName)
            ) {
                return $handler->{$eventName}(
                    $event,
                    $event->getSource(),
                    $event->getData()
                );
            }
            // Model-specific: resolve the event object back to a method name
            // (e.g. BeforeCreateEvent -> "beforeCreate") so non-closure handlers
            // can implement named methods for each model lifecycle event.
            if (
                ($event instanceof AbstractModelEvent) &&
                ($eventName = ModelEventNameEnum::tryFromEventClass($event::class)?->value) &&
                method_exists($handler, $eventName)
            ) {
                return $handler->{$eventName}($event);
            }

            if (method_exists($handler, '__invoke')) {
                return $handler->__invoke($event);
            }
        }

        return $status;
    }

    /**
     * @param string|null $type
     */
    private function processDetachAllNotNullType(string | null $type): void
    {
        if (null !== $type && isset($this->events[$type])) {
            unset($this->events[$type]);
        }
    }

    /**
     * @param string|null $type
     */
    private function processDetachAllNullType(string | null $type): void
    {
        if (null === $type) {
            $this->events = [];
        }
    }
}
