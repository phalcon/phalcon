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
use Phalcon\Contracts\Events\Stoppable;
use Phalcon\Contracts\Events\Subscriber;
use Phalcon\Db\Event\AbstractModelEvent;
use Phalcon\Db\Event\ModelEventNameEnum;
use Phalcon\Events\Exceptions\InvalidEventHandler;
use Phalcon\Events\Exceptions\InvalidEventType;
use Phalcon\Events\Exceptions\InvalidSubscriberConfiguration;
use Phalcon\Events\Exceptions\NoListenersForEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

use function array_splice;
use function array_values;
use function call_user_func_array;
use function count;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function join;
use function method_exists;
use function spl_object_id;
use function strpos;
use function substr;

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
     * Parsed-eventType cache. Memoizes the strpos + substr work done in
     * fire() so the same event name fired repeatedly collapses to a single
     * hash lookup.
     *
     * Shape: `eventNameCache[$eventType] = [typePrefix, eventName]`
     *
     * @var array
     */
    protected array $eventNameCache = [];

    /**
     * Listener storage. Shape:
     *
     *   events[$eventType] = [
     *       [handler, type, priority]            // types 0, 1, 3
     *       [handler, type, priority, className] // type 2 carries
     *                                            // resolved class name
     *       ...
     *   ]
     *
     * `type` is classified once at attach() time so the dispatch loop can
     * route via a simple branch:
     *
     *   0 - Closure
     *   1 - [obj, method] array callable
     *   2 - plain object: method named after the event
     *   3 - generic callable (string fn name, invokable object, etc.)
     *
     * @var array
     */
    protected array $events = [];

    /**
     * Re-entrancy depth of fire()/fireAll(). 0 means no fire is in progress.
     * Used to keep nested fire() calls from clobbering the outer caller's
     * `$this->responses` accumulator.
     *
     * @var int
     */
    protected int $fireDepth = 0;

    /**
     * Manager-level kill switch. When true, every fire()/fireAll()/
     * fireQueue() call returns immediately without dispatching. Cleared by
     * resume().
     *
     * @var bool
     */
    protected bool $halted = false;

    /**
     * Memoized method_exists() results for the plain-object dispatch path.
     * Keyed by `handlerClass => [methodName => bool]`.
     *
     * @var array
     */
    protected array $methodExistsCache = [];

    /**
     * Maximum number of distinct handler classes retained in
     * methodExistsCache. 0 (default) keeps the unbounded behavior.
     *
     * @var int
     */
    protected int $methodExistsCacheLimit = 0;

    /**
     * @var array<array-key, mixed>
     */
    protected array $responses = [];

    /**
     * When true, a listener returning literal `false` (with the event's
     * `cancelable` flag on) short-circuits the dispatch loop and pins the
     * fire() return as `false`. Default off.
     *
     * @var bool
     */
    protected bool $stopOnFalse = false;

    /**
     * When true, fire()/fireAll() throw on dispatch of an event that has zero
     * matching listeners. Default off.
     *
     * @var bool
     */
    protected bool $strict = false;

    /**
     * Memoized getSubscribedEvents() maps keyed by Subscriber class name.
     *
     * @var array
     */
    protected array $subscriberEventsCache = [];

    /**
     * @var array
     */
    protected array $subscribers = [];

    /**
     * Registers an event subscriber. The subscriber's getSubscribedEvents()
     * map is parsed and each entry is attached through the regular listener
     * pipeline.
     *
     * @param Subscriber $subscriber
     *
     * @return void
     */
    public function addSubscriber(Subscriber $subscriber): void
    {
        $this->subscribers[spl_object_id($subscriber)] = $subscriber;

        $className = $subscriber::class;

        if (!isset($this->subscriberEventsCache[$className])) {
            $this->subscriberEventsCache[$className] = $className::getSubscribedEvents();
        }

        foreach ($this->subscriberEventsCache[$className] as $eventName => $params) {
            $this->processSubscriberEntry($subscriber, $eventName, $params, false);
        }
    }

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
     *
     * @return void
     * @throws InvalidEventHandler
     */
    final public function attach(
        string | array $eventType,
        callable | object $handler,
        int $priority = self::DEFAULT_PRIORITY
    ): void {
        if (is_array($eventType)) {
            $eventType = join(':', $eventType);
        }

        // Classify the handler type ONCE so the dispatch loop doesn't have to
        // run instanceof / is_callable per fire per listener.
        if ($handler instanceof Closure) {
            $type = 0;
        } elseif (
            is_array($handler)
            && isset($handler[0], $handler[1])
            && is_object($handler[0])
            && is_string($handler[1])
        ) {
            $type = 1;
        } elseif (is_object($handler)) {
            if (is_callable($handler)) {
                $type = 3;
            } else {
                // Plain object - method named after the event. Capture the
                // class name once at attach time.
                $this->insertHandlerEntry(
                    $eventType,
                    $handler,
                    2,
                    $priority,
                    $handler::class
                );

                return;
            }
        } elseif (is_callable($handler)) {
            $type = 3;
        } else {
            throw new InvalidEventHandler();
        }

        $this->insertHandlerEntry($eventType, $handler, $type, $priority);
    }

    /**
     * Removes every registered subscriber and detaches each listener they
     * contributed. Listeners attached via attach() are untouched.
     *
     * @return void
     */
    public function clearSubscribers(): void
    {
        $snapshot = $this->subscribers;

        foreach ($snapshot as $subscriber) {
            $this->removeSubscriber($subscriber);
        }
    }

    /**
     * Tells the event manager if it needs to collect all the responses returned
     * by every registered listener in a single fire
     *
     * @param bool $collect
     *
     * @return void
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
     * @throws InvalidEventHandler
     */
    public function detach(string $eventType, object | callable $handler): void
    {
        if (false === $this->isValidHandler($handler)) {
            throw new InvalidEventHandler();
        }

        if (!isset($this->events[$eventType])) {
            return;
        }

        $newQueue = [];
        foreach ($this->events[$eventType] as $existing) {
            if ($existing[0] !== $handler) {
                $newQueue[] = $existing;
            }
        }

        // Drop the key when the last listener is gone so fire() can
        // short-circuit cleanly and hasListeners() tells the truth.
        if (count($newQueue) > 0) {
            $this->events[$eventType] = $newQueue;
        } else {
            unset($this->events[$eventType]);
        }
    }

    /**
     * Removes all events from the EventsManager
     *
     * @param string|null $type
     *
     * @return void
     */
    public function detachAll(string | null $type = null): void
    {
        if (null === $type) {
            $this->events = [];

            return;
        }

        unset($this->events[$type]);
    }

    /**
     * Dispatches an object event to the appropriate event listeners.
     *
     * PSR-14 shaped: listeners receive the (possibly mutated) event object.
     * Propagation stops when the event implements
     * {@see StoppableEventInterface} and reports it is stopped.
     *
     * @param object               $event  The event object to be dispatched.
     * @param string|string[]|null $name   Optional event name to look for.
     * @param object|null          $source Optional source object.
     *
     * @return mixed The last listener result or null when no listener matches.
     */
    public function dispatch(
        object $event,
        string | array | null $name = null,
        ?object $source = null
    ): mixed {
        if (empty($this->events)) {
            return null;
        }

        $methodName = null;

        if (is_array($name)) {
            $methodName = $name[1] ?? null;
            $name       = join(':', $name);
        } elseif (is_string($name)) {
            $colonPos = strpos($name, ':');
            if (false !== $colonPos) {
                $methodName = substr($name, $colonPos + 1);
            }
        }

        if (null !== $name && !empty($this->events[$name])) {
            return $this->runObjectQueue($this->events[$name], $event, $methodName);
        }

        $eventClassName = $event::class;
        if (!empty($this->events[$eventClassName])) {
            return $this->runObjectQueue($this->events[$eventClassName], $event, $methodName);
        }

        return null;
    }

    /**
     * Set if priorities are enabled in the EventsManager.
     *
     * @param bool $enablePriorities
     *
     * @return void
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
     *
     * @param string $eventType
     * @param object $source
     * @param mixed  $data
     * @param bool   $cancelable
     *
     * @return mixed
     * @throws InvalidEventType
     * @throws NoListenersForEvent
     */
    public function fire(
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true
    ): mixed {
        // Manager-level kill switch.
        if ($this->halted) {
            return null;
        }

        if (false === $this->beforeFire($eventType, $source, $data, $cancelable)) {
            return null;
        }

        // Fast exit on a manager with no listeners attached at all.
        if (empty($this->events)) {
            if ($this->strict) {
                throw new NoListenersForEvent($eventType);
            }

            return null;
        }

        if (isset($this->eventNameCache[$eventType])) {
            [$type, $eventName] = $this->eventNameCache[$eventType];
        } else {
            $colonPos = strpos($eventType, ':');

            if (false === $colonPos) {
                // PSR-14 bridge: a colon-less event type with an object as
                // its data is delegated to dispatch() so object events can be
                // fired through the legacy fire() entry point.
                if (is_object($data)) {
                    return $this->dispatch($data, $eventType, $source);
                }

                throw new InvalidEventType($eventType);
            }

            $type      = substr($eventType, 0, $colonPos);
            $eventName = substr($eventType, $colonPos + 1);

            $this->eventNameCache[$eventType] = [$type, $eventName];
        }

        $hasTypeQueue = isset($this->events[$type]);
        $hasFullQueue = isset($this->events[$eventType]);

        if (!$hasTypeQueue && !$hasFullQueue) {
            if ($this->strict) {
                throw new NoListenersForEvent($eventType);
            }

            return null;
        }

        $wasDepth        = $this->fireDepth;
        $this->fireDepth = $wasDepth + 1;
        $collect         = $this->collect;
        $stashed         = [];

        if ($collect) {
            if ($wasDepth > 0) {
                $stashed = $this->responses;
            }

            $this->responses = [];
        }

        try {
            $event  = new Event($eventName, $source, $data, $cancelable);
            $status = null;

            if ($hasTypeQueue) {
                $status = $this->runQueue(
                    $this->events[$type],
                    $event,
                    $eventName,
                    $source,
                    $data,
                    $cancelable,
                    $collect
                );
            }

            if (
                !($this->stopOnFalse && $cancelable && false === $status)
                && $hasFullQueue
                && (!$cancelable || !$event->isStopped())
            ) {
                $status = $this->runQueue(
                    $this->events[$eventType],
                    $event,
                    $eventName,
                    $source,
                    $data,
                    $cancelable,
                    $collect
                );
            }
        } catch (Throwable $ex) {
            if ($collect && $wasDepth > 0) {
                $this->responses = $stashed;
            }

            $this->fireDepth = $wasDepth;

            throw $ex;
        }

        if ($collect && $wasDepth > 0) {
            $this->responses = $stashed;
        }

        $this->fireDepth = $wasDepth;

        return $this->afterFire($status, $eventType, $source, $data, $cancelable);
    }

    /**
     * Fires an event and returns every listener's return value as an indexed
     * array. Independent of collectResponses(); the caller's collected state
     * on `$this->responses` is preserved (stashed and restored).
     *
     * @param string $eventType
     * @param object $source
     * @param mixed  $data
     * @param bool   $cancelable
     *
     * @return array
     * @throws InvalidEventType
     * @throws NoListenersForEvent
     */
    public function fireAll(
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true
    ): array {
        if ($this->halted) {
            return [];
        }

        if (empty($this->events)) {
            if ($this->strict) {
                throw new NoListenersForEvent($eventType);
            }

            return [];
        }

        if (isset($this->eventNameCache[$eventType])) {
            [$type, $eventName] = $this->eventNameCache[$eventType];
        } else {
            $colonPos = strpos($eventType, ':');

            if (false === $colonPos) {
                throw new InvalidEventType($eventType);
            }

            $type      = substr($eventType, 0, $colonPos);
            $eventName = substr($eventType, $colonPos + 1);

            $this->eventNameCache[$eventType] = [$type, $eventName];
        }

        $hasTypeQueue = isset($this->events[$type]);
        $hasFullQueue = isset($this->events[$eventType]);

        if (!$hasTypeQueue && !$hasFullQueue) {
            if ($this->strict) {
                throw new NoListenersForEvent($eventType);
            }

            return [];
        }

        $wasDepth        = $this->fireDepth;
        $this->fireDepth = $wasDepth + 1;
        $stashed         = $this->responses;
        $this->responses = [];

        try {
            $event          = new Event($eventName, $source, $data, $cancelable);
            $dispatchStatus = null;

            if ($hasTypeQueue) {
                $dispatchStatus = $this->runQueue(
                    $this->events[$type],
                    $event,
                    $eventName,
                    $source,
                    $data,
                    $cancelable,
                    true
                );
            }

            if (
                !($this->stopOnFalse && $cancelable && false === $dispatchStatus)
                && $hasFullQueue
                && (!$cancelable || !$event->isStopped())
            ) {
                $this->runQueue(
                    $this->events[$eventType],
                    $event,
                    $eventName,
                    $source,
                    $data,
                    $cancelable,
                    true
                );
            }
        } catch (Throwable $ex) {
            $this->responses = $stashed;
            $this->fireDepth = $wasDepth;

            throw $ex;
        }

        $responses       = $this->responses;
        $this->responses = $stashed;
        $this->fireDepth = $wasDepth;

        return $responses;
    }

    /**
     * Internal handler to call a queue of events.
     *
     * Kept as a thin BC wrapper around the private dispatch loop.
     *
     * @param array          $queue
     * @param EventInterface $event
     *
     * @return mixed
     */
    final public function fireQueue(array $queue, EventInterface $event): mixed
    {
        if ($this->halted) {
            return null;
        }

        return $this->runQueue(
            $queue,
            $event,
            $event->getType(),
            $event->getSource(),
            $event->getData(),
            $event->isCancelable(),
            $this->collect
        );
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
        $listeners = [];

        if (isset($this->events[$type])) {
            foreach ($this->events[$type] as $existing) {
                $listeners[] = $existing[0];
            }
        }

        return $listeners;
    }

    /**
     * Returns the configured method_exists-cache cap (0 = unlimited).
     *
     * @return int
     */
    public function getMethodExistsCacheLimit(): int
    {
        return $this->methodExistsCacheLimit;
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
     * Returns the list of registered subscriber instances.
     *
     * @return array
     */
    public function getSubscribers(): array
    {
        return array_values($this->subscribers);
    }

    /**
     * Manager-level kill switch. After halt(), every fire()/fireAll()/
     * fireQueue() call returns immediately without dispatching, until
     * resume() is called.
     *
     * @return void
     */
    public function halt(): void
    {
        $this->halted = true;
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
     * Check if the events manager is collecting all the responses returned by
     * every registered listener in a single fire
     *
     * @return bool
     */
    public function isCollecting(): bool
    {
        return $this->collect;
    }

    /**
     * Returns whether the manager-level kill switch is engaged. See halt().
     *
     * @return bool
     */
    public function isHalted(): bool
    {
        return $this->halted;
    }

    /**
     * Returns whether the stop-on-false short-circuit is enabled.
     *
     * @return bool
     */
    public function isStopOnFalse(): bool
    {
        return $this->stopOnFalse;
    }

    /**
     * Returns whether strict mode is enabled.
     *
     * @return bool
     */
    public function isStrict(): bool
    {
        return $this->strict;
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
     * Removes a previously registered subscriber. Detaches every listener the
     * subscriber declared via getSubscribedEvents(). Idempotent.
     *
     * @param Subscriber $subscriber
     *
     * @return void
     */
    public function removeSubscriber(Subscriber $subscriber): void
    {
        $key = spl_object_id($subscriber);

        if (!isset($this->subscribers[$key])) {
            return;
        }

        unset($this->subscribers[$key]);

        $className = $subscriber::class;

        if (!isset($this->subscriberEventsCache[$className])) {
            $this->subscriberEventsCache[$className] = $className::getSubscribedEvents();
        }

        foreach ($this->subscriberEventsCache[$className] as $eventName => $params) {
            $this->processSubscriberEntry($subscriber, $eventName, $params, true);
        }
    }

    /**
     * Clears the manager-level kill switch set by halt().
     *
     * @return void
     */
    public function resume(): void
    {
        $this->halted = false;
    }

    /**
     * Caps the number of distinct handler classes retained in the
     * method_exists memoization cache. 0 disables the cap.
     *
     * @param int $methodExistsCacheLimit
     *
     * @return void
     */
    public function setMethodExistsCacheLimit(int $methodExistsCacheLimit): void
    {
        $this->methodExistsCacheLimit = $methodExistsCacheLimit;
    }

    /**
     * Enables/disables the stop-on-false short-circuit. Default off.
     *
     * @param bool $flag
     *
     * @return void
     */
    public function setStopOnFalse(bool $flag): void
    {
        $this->stopOnFalse = $flag;
    }

    /**
     * Enables/disables strict mode. When true, fire()/fireAll() throw when
     * dispatching an event with zero matching listeners.
     *
     * @param bool $strict
     *
     * @return void
     */
    public function setStrict(bool $strict): void
    {
        $this->strict = $strict;
    }

    /**
     * Extension seam invoked after an event has been dispatched to its
     * listener queues. The base implementation returns `status` unchanged.
     *
     * @param mixed  $status
     * @param string $eventType
     * @param object $source
     * @param mixed  $data
     * @param bool   $cancelable
     *
     * @return mixed
     */
    protected function afterFire(
        mixed $status,
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true
    ): mixed {
        return $status;
    }

    /**
     * Extension seam invoked before an event is dispatched. The base
     * implementation returns true, so dispatch proceeds. Returning false
     * aborts the dispatch entirely.
     *
     * @param string $eventType
     * @param object $source
     * @param mixed  $data
     * @param bool   $cancelable
     *
     * @return bool
     */
    protected function beforeFire(
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true
    ): bool {
        return true;
    }

    /**
     * Stores a pre-classified listener tuple in the queue for an event type.
     *
     * type=2 tuples carry a 4th element `className` so the dispatch loop can
     * skip the per-fire get_class() lookup against methodExistsCache.
     *
     * @param string $eventType
     * @param mixed  $handler
     * @param int    $type
     * @param int    $priority
     * @param mixed  $className
     *
     * @return void
     */
    private function insertHandlerEntry(
        string $eventType,
        mixed $handler,
        int $type,
        int $priority,
        mixed $className = null
    ): void {
        $prioritiesOn = $this->enablePriorities;

        if (!$prioritiesOn) {
            $priority = self::DEFAULT_PRIORITY;
        }

        if (2 === $type) {
            $tuple = [$handler, $type, $priority, $className];
        } else {
            $tuple = [$handler, $type, $priority];
        }

        if (!isset($this->events[$eventType])) {
            $this->events[$eventType] = [$tuple];

            return;
        }

        $queue = $this->events[$eventType];

        // Priorities disabled (the default): append and return.
        if (!$prioritiesOn) {
            $queue[]                  = $tuple;
            $this->events[$eventType] = $queue;

            return;
        }

        // Sorted-insert: descending priority, FIFO within same priority.
        $insertAt = -1;

        foreach ($queue as $index => $existing) {
            if ($existing[2] < $priority) {
                $insertAt = $index;

                break;
            }
        }

        if (-1 === $insertAt) {
            $queue[]                  = $tuple;
            $this->events[$eventType] = $queue;

            return;
        }

        array_splice($queue, $insertAt, 0, [$tuple]);
        $this->events[$eventType] = $queue;
    }

    /**
     * Parses one entry of a subscriber's getSubscribedEvents() map and either
     * attaches or detaches the resulting listeners.
     *
     * @param object $subscriber
     * @param string $eventName
     * @param mixed  $params
     * @param bool   $detaching
     *
     * @return void
     * @throws InvalidSubscriberConfiguration
     */
    private function processSubscriberEntry(
        object $subscriber,
        string $eventName,
        mixed $params,
        bool $detaching
    ): void {
        if (is_string($params)) {
            if ($detaching) {
                $this->detach($eventName, [$subscriber, $params]);
            } else {
                $this->insertHandlerEntry(
                    $eventName,
                    [$subscriber, $params],
                    1,
                    self::DEFAULT_PRIORITY
                );
            }

            return;
        }

        if (!is_array($params)) {
            throw new InvalidSubscriberConfiguration($eventName);
        }

        if (!isset($params[0])) {
            throw new InvalidSubscriberConfiguration($eventName);
        }

        $firstParam = $params[0];

        if (is_string($firstParam)) {
            $methodName = $firstParam;
            $priority   = $params[1] ?? self::DEFAULT_PRIORITY;

            if ($detaching) {
                $this->detach($eventName, [$subscriber, $methodName]);
            } else {
                $this->insertHandlerEntry(
                    $eventName,
                    [$subscriber, $methodName],
                    1,
                    $priority
                );
            }

            return;
        }

        if (is_array($firstParam)) {
            foreach ($params as $listener) {
                $methodName = $listener[0];
                $priority   = $listener[1] ?? self::DEFAULT_PRIORITY;

                if ($detaching) {
                    $this->detach($eventName, [$subscriber, $methodName]);
                } else {
                    $this->insertHandlerEntry(
                        $eventName,
                        [$subscriber, $methodName],
                        1,
                        $priority
                    );
                }
            }

            return;
        }

        throw new InvalidSubscriberConfiguration($eventName);
    }

    /**
     * Object-event dispatch loop used by dispatch(). Generic:
     * closure/callable handlers receive the event object; plain-object
     * handlers resolve to the model lifecycle method (via ModelEventNameEnum)
     * or fall back to __invoke. Propagation stops when the event implements
     * StoppableEventInterface and reports it is stopped.
     *
     * @param array  $queue
     * @param object $event
     *
     * @return mixed
     */
    private function runObjectQueue(
        array $queue,
        object $event,
        ?string $methodName = null
    ): mixed {
        $status  = null;
        $collect = $this->collect;

        foreach ($queue as $tuple) {
            $handler = $tuple[0];
            $type    = $tuple[1];

            if (0 === $type || 1 === $type || 3 === $type) {
                $ret = $handler($event);
            } else {
                // type 2: plain object handler.
                if (null !== $methodName && method_exists($handler, $methodName)) {
                    $ret = $handler->{$methodName}($event);
                } elseif (
                    $event instanceof AbstractModelEvent
                    && null !== ($modelMethod = ModelEventNameEnum::tryFromEventClass($event::class)?->value)
                    && method_exists($handler, $modelMethod)
                ) {
                    // Bridge: model lifecycle event -> resolved method.
                    $ret = $handler->{$modelMethod}($event);
                } elseif (method_exists($handler, '__invoke')) {
                    $ret = $handler->__invoke($event);
                } else {
                    continue;
                }
            }

            if ($collect) {
                $this->responses[] = $ret;
            }

            $status = $ret;

            if (
                ($event instanceof Stoppable && $event->isPropagationStopped())
                || ($event instanceof StoppableEventInterface && $event->isPropagationStopped())
            ) {
                break;
            }
        }

        return $status;
    }

    /**
     * Hot dispatch loop for string events. Called by fire()/fireAll() with
     * hoisted args and by fireQueue() as a BC wrapper. Owns the aggregation
     * contract:
     *
     * 1. Last non-null wins.
     * 2. stop() determinism: a listener that stops the event makes its return
     *    the dispatch return (even if null) and the queue is abandoned.
     *
     * @param array          $queue
     * @param EventInterface $event
     * @param string         $eventName
     * @param mixed          $source
     * @param mixed          $data
     * @param bool           $cancelable
     * @param bool           $collect
     *
     * @return mixed
     */
    private function runQueue(
        array $queue,
        EventInterface $event,
        string $eventName,
        mixed $source,
        mixed $data,
        bool $cancelable,
        bool $collect
    ): mixed {
        $status    = null;
        $queueSize = count($queue);

        // Single-handler fast path.
        if (1 === $queueSize) {
            $tuple   = $queue[0];
            $handler = $tuple[0];
            $type    = $tuple[1];

            if (0 === $type) {
                $ret = $handler($event, $source, $data);
            } elseif (1 === $type) {
                $ret = $handler[0]->{$handler[1]}($event, $source, $data);
            } elseif (2 === $type) {
                $handlerClass = $tuple[3];

                if (!isset($this->methodExistsCache[$handlerClass][$eventName])) {
                    if (
                        !isset($this->methodExistsCache[$handlerClass])
                        && $this->methodExistsCacheLimit > 0
                        && count($this->methodExistsCache) >= $this->methodExistsCacheLimit
                    ) {
                        $this->methodExistsCache = [];
                    }

                    $this->methodExistsCache[$handlerClass][$eventName] = method_exists($handler, $eventName);
                }

                if (!$this->methodExistsCache[$handlerClass][$eventName]) {
                    return $status;
                }

                $ret = $handler->{$eventName}($event, $source, $data);
            } else {
                $ret = call_user_func_array($handler, [$event, $source, $data]);
            }

            if ($collect) {
                $this->responses[] = $ret;
            }

            if ($this->stopOnFalse && $cancelable && false === $ret) {
                return false;
            }

            return $ret;
        }

        foreach ($queue as $tuple) {
            $handler = $tuple[0];
            $type    = $tuple[1];

            if (0 === $type) {
                $ret = $handler($event, $source, $data);
            } elseif (1 === $type) {
                $ret = $handler[0]->{$handler[1]}($event, $source, $data);
            } elseif (2 === $type) {
                $handlerClass = $tuple[3];

                if (!isset($this->methodExistsCache[$handlerClass][$eventName])) {
                    if (
                        !isset($this->methodExistsCache[$handlerClass])
                        && $this->methodExistsCacheLimit > 0
                        && count($this->methodExistsCache) >= $this->methodExistsCacheLimit
                    ) {
                        $this->methodExistsCache = [];
                    }

                    $this->methodExistsCache[$handlerClass][$eventName] = method_exists($handler, $eventName);
                }

                if (!$this->methodExistsCache[$handlerClass][$eventName]) {
                    continue;
                }

                $ret = $handler->{$eventName}($event, $source, $data);
            } else {
                $ret = call_user_func_array($handler, [$event, $source, $data]);
            }

            if ($collect) {
                $this->responses[] = $ret;
            }

            if ($this->stopOnFalse && $cancelable && false === $ret) {
                return false;
            }

            if ($cancelable && $event->isStopped()) {
                return $ret;
            }

            if (null !== $ret) {
                $status = $ret;
            }
        }

        return $status;
    }
}
