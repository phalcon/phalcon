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
use Phalcon\Contracts\Events\Enumerable;
use Phalcon\Contracts\Events\EventsTypes;
use Phalcon\Contracts\Events\Stoppable;
use Phalcon\Contracts\Events\Subscriber;
use Phalcon\Events\Exceptions\InvalidEventHandler;
use Phalcon\Events\Exceptions\InvalidEventType;
use Phalcon\Events\Exceptions\InvalidSubscriberConfiguration;
use Phalcon\Events\Exceptions\NoListenersForEvent;
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
 *
 * @phpstan-import-type events_method_exists_cache from EventsTypes
 * @phpstan-import-type events_name_cache from EventsTypes
 * @phpstan-import-type events_queue from EventsTypes
 * @phpstan-import-type events_storage from EventsTypes
 * @phpstan-import-type events_subscriber_events_cache from EventsTypes
 * @phpstan-import-type events_subscriber_listener from EventsTypes
 * @phpstan-import-type events_subscribers from EventsTypes
 */
class Manager implements ManagerInterface, Enumerable
{
    protected bool $collect = false;

    protected bool $enablePriorities = false;

    /**
     * Parsed-eventType cache. Memoizes the strpos + substr work done in
     * fire() so the same event name fired repeatedly (the common case
     * for db:beforeQuery, model:afterSave, etc.) collapses to a single
     * hash lookup.
     *
     * Shape: `eventNameCache[$eventType] = [typePrefix, eventName]`
     *
     * Unbounded by design - distinct event types in a typical Phalcon
     * application are well under 100 keys, and the cache never needs
     * invalidation (parse is deterministic for a given eventType string).
     *
     * @phpstan-var events_name_cache
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
     * Kept sorted by priority descending when priorities are enabled
     * (FIFO within the same priority); otherwise listeners are simply
     * appended in attach order.
     *
     * `type` is classified once at attach() time so dispatch() can
     * route via a simple branch:
     *
     *   0 - Closure: direct invocation via `{handler}(args)`, no
     *       arg-array alloc per call
     *   1 - [obj, method] array callable: direct dynamic dispatch
     *       `handler[0]->{handler[1]}(args)`
     *   2 - plain object: dynamic dispatch via method named after the
     *       event (the classic Phalcon listener pattern); class name is
     *       captured at attach time to skip get_class() per fire
     *   3 - generic callable (string fn name, invokable object,
     *       [class, staticMethod]): call_user_func_array
     *
     * @phpstan-var events_storage
     */
    protected array $events = [];

    /**
     * Re-entrancy depth of fire()/fireAll(). 0 means no fire is in
     * progress. Incremented on every fire entry, decremented on exit.
     * Used to keep nested fire() calls from clobbering the outer
     * caller's `$this->responses` accumulator.
     */
    protected int $fireDepth = 0;

    /**
     * Manager-level kill switch. When true, every fire()/fireAll()/
     * fireQueue() call returns immediately (null or empty array) without
     * dispatching. Cleared by resume(). Survives across fire() calls,
     * unlike Event::stop() which only stops the current dispatch chain.
     */
    protected bool $halted = false;

    /**
     * Memoized method_exists() results for the OBJECT_METHOD dispatch
     * path in dispatch(). Keyed by `handlerClass => [methodName => bool]`.
     * A class doesn't gain methods at runtime so the lookup is permanent.
     *
     * @phpstan-var events_method_exists_cache
     */
    protected array $methodExistsCache = [];

    /**
     * Maximum number of distinct handler classes retained in
     * methodExistsCache. 0 (default) keeps the original unbounded
     * behavior; a positive value clears the cache when adding a new
     * class would exceed it. Re-warming is cheap (method_exists is
     * O(1)) and the cap is meant for very long-lived workers that see
     * many distinct listener classes over time.
     */
    protected int $methodExistsCacheLimit = 0;

    /**
     * @var array<array-key, mixed>
     */
    protected array $responses = [];

    /**
     * When true, a listener returning literal `false` (with the event's
     * `cancelable` flag on) short-circuits the dispatch loop and pins
     * the fire() return as `false`. Default off - preserves the pre-5.13
     * "last-wins" contract for codebases that rely on later listeners
     * overriding an earlier false return [#17019].
     */
    protected bool $stopOnFalse = false;

    /**
     * When true, fire()/fireAll() throw on dispatch of an event that
     * has zero matching listeners. Catches typos in dev. Default off.
     */
    protected bool $strict = false;

    /**
     * Memoized getSubscribedEvents() maps keyed by Subscriber class name.
     * The static method's return is stable for the lifetime of a class
     * definition, so the cache never needs invalidation.
     *
     * @phpstan-var events_subscriber_events_cache
     */
    protected array $subscriberEventsCache = [];

    /**
     * @phpstan-var events_subscribers
     */
    protected array $subscribers = [];

    /**
     * Registers an event subscriber. The subscriber's getSubscribedEvents()
     * map is parsed and each entry is attached through the regular listener
     * pipeline.
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
     */
    public function arePrioritiesEnabled(): bool
    {
        return $this->enablePriorities;
    }

    /**
     * Attach a listener to the events manager
     *
     * @throws InvalidEventHandler
     */
    final public function attach(
        string $eventType,
        mixed $handler,
        int $priority = self::DEFAULT_PRIORITY
    ): void {
        // Classify the handler type ONCE so fireQueue() doesn't have to
        // run instanceof / is_callable per fire per listener.
        //
        //   0 - Closure: direct invocation via Zephir {handler}(args)
        //   1 - [obj, method] array callable: direct dynamic dispatch
        //   2 - plain object, method named after the event (classic Phalcon)
        //   3 - generic callable: string function, invokable object,
        //       [class, staticMethod] etc.
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
     * Iterates a snapshot of `subscribers` so removeSubscriber() can safely
     * mutate the original property during the walk.
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
     */
    public function collectResponses(bool $collect): void
    {
        $this->collect = $collect;
    }

    /**
     * Detach the listener from the events manager
     *
     * @throws InvalidEventHandler
     */
    public function detach(string $eventType, mixed $handler): void
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
        if (!empty($newQueue)) {
            $this->events[$eventType] = $newQueue;
        } else {
            unset($this->events[$eventType]);
        }
    }

    /**
     * Removes all events from the EventsManager
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
     * Dispatches an object event to its listeners, routed by an explicit name
     * (a string, or a [class, method] array) or, failing that, by the event's
     * class name. Listeners receive the event object. Propagation stops when
     * the event implements Phalcon\Contracts\Events\Stoppable and reports it
     * is stopped.
     *
     * @param string|string[]|null $name
     * @param object|null          $source
     */
    public function dispatch(
        object $event,
        mixed $name = null,
        mixed $source = null
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
        } else {
            $name = null;
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
     * A priority queue of events is a data structure similar
     * to a regular queue of events: we can also put and extract
     * elements from it. The difference is that each element in a
     * priority queue is associated with a value called priority.
     * This value is used to order elements of a queue: elements
     * with higher priority are retrieved before the elements with
     * lower priority.
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
     * @param bool|null $stopOnFalse
     *
     * @throws InvalidEventType
     * @throws NoListenersForEvent
     */
    public function fire(
        string $eventType,
        object $source,
        mixed $data = null,
        bool $cancelable = true,
        mixed $stopOnFalse = null
    ): mixed {
        /**
         * Per-call override of setStopOnFalse(): `true` makes a listener's
         * `false` final for this fire only, `false` keeps last-wins, `null`
         * uses the manager setting. Not part of the Manager contract.
         */
        if (null === $stopOnFalse) {
            $stop = $this->stopOnFalse;
        } else {
            $stop = (bool) $stopOnFalse;
        }

        // Manager-level kill switch - halt() trips this and every fire
        // returns null without dispatching until resume() clears it.
        if ($this->halted) {
            return null;
        }

        if (false === $this->beforeFire($eventType, $source, $data, $cancelable)) {
            return null;
        }

        // Fast exit on a manager with no listeners attached at all.
        // Done BEFORE parsing the eventType so a misformed name (no
        // colon) doesn't raise "Invalid event type" on a manager that
        // would have had nothing to dispatch to anyway.
        if (empty($this->events)) {
            if ($this->strict) {
                throw new NoListenersForEvent($eventType);
            }

            return null;
        }

        // Cache hit: the eventType parse is deterministic, and the same
        // names fire over and over (db:beforeQuery × N per request etc.).
        // After warm-up this collapses to a single hash lookup.
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

        // Short-circuit BEFORE allocating Event: in production most fires
        // have zero matching listeners (a model lifecycle event with no
        // user-attached behavior, a DB event without a tracer, etc.).
        if (!$hasTypeQueue && !$hasFullQueue) {
            if ($this->strict) {
                throw new NoListenersForEvent($eventType);
            }

            return null;
        }

        // Increment reentrancy depth. Nested fire() calls stash and
        // restore $this->responses so the outer caller's collected
        // state is never clobbered.
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

        // Wrap dispatch in try/catch so a throwing listener cannot
        // leak the incremented fireDepth or the stashed responses -
        // important for long-lived managers (workers, daemons) where
        // a single dirty teardown would poison every subsequent fire.
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
                    $collect,
                    $stop
                );
            }

            // stopOnFalse propagation: dispatch already short-circuited
            // its queue; skip the fully-qualified queue too and pin
            // the fire() return as false.
            if (
                !($stop && $cancelable && false === $status)
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
                    $collect,
                    $stop
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
     * Fires an event and returns every listener's return value as an
     * indexed array. Independent of collectResponses(); the caller's
     * collected state on `$this->responses` is preserved (stashed and
     * restored across the call).
     *
     *```php
     * $results = $eventsManager->fireAll("db:beforeQuery", $connection);
     *```
     *
     * @return array<array-key, mixed>
     *
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
                    true,
                    $this->stopOnFalse
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
                    true,
                    $this->stopOnFalse
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
     * Kept at its original 2-arg signature for BC; thin wrapper around
     * the private `dispatch()` helper. Direct callers pay the cost of
     * re-extracting metadata from the Event; the framework's own fire()
     * path bypasses this wrapper and calls dispatch() with hoisted args.
     *
     * @phpstan-param events_queue $queue
     */
    final public function fireQueue(array $queue, EventInterface $event): mixed
    {
        if ($this->halted) {
            return null;
        }

        /**
         * The contract does not declare getSource(), and its getType() gives
         * mixed. Event declares both. An event without getSource() fails
         * here at run time. Remove this when the contract declares both.
         *
         * @phpstan-var Event $event
         */
        return $this->runQueue(
            $queue,
            $event,
            $event->getType(),
            $event->getSource(),
            $event->getData(),
            $event->isCancelable(),
            $this->collect,
            $this->stopOnFalse
        );
    }

    /**
     * Returns every event type that currently has at least one listener,
     * mapped to that type's listeners. Types contributed by subscribers are
     * included, because addSubscriber() attaches through the regular listener
     * pipeline.
     *
     * Unwrapping is delegated to getListeners() so the internal shape of
     * $this->events is read in exactly one place.
     *
     * @return array<string, array<array-key, mixed>>
     */
    public function getListenerMap(): array
    {
        $map = [];

        foreach (array_keys($this->events) as $type) {
            $map[$type] = $this->getListeners($type);
        }

        return $map;
    }

    /**
     * Returns all the attached listeners of a certain type
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
     * See setMethodExistsCacheLimit().
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
     * Returns the list of registered subscriber instances. Useful for
     * introspection and test setup/teardown.
     *
     * @phpstan-return list<Subscriber>
     */
    public function getSubscribers(): array
    {
        return array_values($this->subscribers);
    }

    /**
     * Manager-level kill switch. After halt(), every fire()/fireAll()/
     * fireQueue() call returns immediately without dispatching, until
     * resume() is called. Use this when a listener needs to abort all
     * subsequent event activity for the lifetime of the manager (e.g.
     * a security check that cancels everything downstream).
     */
    public function halt(): void
    {
        $this->halted = true;
    }

    /**
     * Check whether certain type of event has listeners
     */
    public function hasListeners(string $type): bool
    {
        return isset($this->events[$type]);
    }

    /**
     * Check if the events manager is collecting all all the responses returned
     * by every registered listener in a single fire
     */
    public function isCollecting(): bool
    {
        return $this->collect;
    }

    /**
     * Returns whether the manager-level kill switch is engaged. See halt().
     */
    public function isHalted(): bool
    {
        return $this->halted;
    }

    /**
     * Returns whether the stop-on-false short-circuit is enabled.
     * See setStopOnFalse().
     */
    public function isStopOnFalse(): bool
    {
        return $this->stopOnFalse;
    }

    /**
     * Returns whether strict mode is enabled. When true, fire()/fireAll()
     * throw when an event has no matching listeners - useful in dev to
     * catch typos. Default off.
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    public function isValidHandler(mixed $handler): bool
    {
        if (!is_object($handler) && !is_callable($handler)) {
            return false;
        }

        return true;
    }

    /**
     * Removes a previously registered subscriber. Detaches every listener the
     * subscriber declared via getSubscribedEvents(). Idempotent - calling
     * with a subscriber that was never added (or already removed) is a no-op.
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
     * Clears the manager-level kill switch set by halt(). Subsequent
     * fire()/fireAll()/fireQueue() calls resume normal dispatch.
     */
    public function resume(): void
    {
        $this->halted = false;
    }

    /**
     * Caps the number of distinct handler classes retained in the
     * method_exists memoization cache. 0 disables the cap (the
     * default; preserves the original unbounded behavior). When the
     * cap is exceeded, the cache is cleared and re-warms on subsequent
     * fires.
     */
    public function setMethodExistsCacheLimit(int $methodExistsCacheLimit): void
    {
        $this->methodExistsCacheLimit = $methodExistsCacheLimit;
    }

    /**
     * Enables/disables the stop-on-false short-circuit. When true, a
     * listener returning literal `false` (with cancelable=true) stops
     * the current event's queue and pins the fire() return as `false`.
     * Later listeners cannot overwrite the cancel. Default off.
     *
     * Independent of halt() / event->stop() - only governs how the
     * dispatch loop reacts to a `false` listener return.
     */
    public function setStopOnFalse(bool $flag): void
    {
        $this->stopOnFalse = $flag;
    }

    /**
     * Enables/disables strict mode. When true, fire()/fireAll() throw
     * when dispatching an event with zero matching listeners.
     */
    public function setStrict(bool $strict): void
    {
        $this->strict = $strict;
    }

    /**
     * Extension seam invoked after an event has been dispatched to its
     * listener queues. Receives the computed dispatch result as `status`
     * and returns the value fire() hands back to its caller; the base
     * implementation returns `status` unchanged. A subclass can override
     * it to run bookkeeping or to post-process / rewrite the result.
     *
     * Only called when the event was actually dispatched; the halted and
     * no-listener short-circuits in fire() return before reaching it.
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
     * implementation returns true, so dispatch proceeds unchanged. A
     * subclass can override it to inspect the source and data and, by
     * returning false, abort the dispatch entirely - for example to
     * redirect a deferred event onto an external queue. Invoked before the
     * no-listener short-circuits, so it sees every fire(), including those
     * with no locally attached listeners.
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
     * Stores a pre-classified listener tuple in the queue for an event
     * type. Bypasses attach()'s type classification - callers that
     * already know the type (the subscriber path) skip the instanceof /
     * is_callable cascade.
     *
     * type=2 tuples carry a 4th element `className` so dispatch() can
     * skip the per-fire get_class() lookup against methodExistsCache.
     *
     * @phpstan-param string|null $className
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
     * attaches or detaches the resulting listeners depending on `detaching`.
     *
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
            /**
             * The Subscriber contract gives this entry as [method, priority].
             *
             * @phpstan-var events_subscriber_listener $params
             */
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
                /**
                 * The Subscriber contract gives each entry as
                 * [method, priority].
                 *
                 * @phpstan-var events_subscriber_listener $listener
                 */
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
     * Object-event dispatch loop used by dispatch(). Closure/callable handlers
     * receive the event object; plain-object handlers call the method named by
     * the dispatch name (when provided) or fall back to __invoke. Propagation
     * stops when the event implements Phalcon\Contracts\Events\Stoppable and
     * reports it is stopped.
     *
     * The listener type that attach() sets gives the handler shape. PHPStan
     * cannot follow that link, thus each branch declares the shape.
     *
     * @phpstan-param events_queue $queue
     * @phpstan-param string|null  $methodName
     */
    private function runObjectQueue(
        array $queue,
        object $event,
        mixed $methodName
    ): mixed {
        $status  = null;
        $collect = $this->collect;

        foreach ($queue as $tuple) {
            $handler = $tuple[0];
            $type    = $tuple[1];

            if (0 === $type || 1 === $type || 3 === $type) {
                /** @phpstan-var callable $handler */
                $ret = $handler($event);
            } else {
                /**
                 * type 2: plain object handler.
                 *
                 * @phpstan-var object $handler
                 */
                if (null !== $methodName && method_exists($handler, $methodName)) {
                    $ret = $handler->{$methodName}($event);
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

            if ($event instanceof Stoppable && $event->isPropagationStopped()) {
                break;
            }
        }

        return $status;
    }

    /**
     * Hot dispatch loop. Called by fire()/fireAll() with hoisted args,
     * and by fireQueue() as a BC wrapper. Owns the documented
     * aggregation contract:
     *
     * 1. **Last non-null wins** - `status` only updates when a listener
     *    returns a non-null value. A chain of nulls leaves the last
     *    real return intact.
     * 2. **stop() determinism** - when a listener calls
     *    `$event->stop()` (and cancelable=true), that listener's
     *    return value becomes the dispatch return - even if null.
     *
     * Note: returning `false` from a listener does **not** short-circuit
     * the queue. Callers that want to stop downstream listeners must call
     * `$event->stop()`. (Some consumers, like the dispatcher, check the
     * return value of `fire()` for `false` and act on it themselves; that
     * remains in their own dispatch logic.)
     *
     * Appends every listener's return to $this->responses when
     * `collect` is true (the caller manages stashing/restoring around
     * nested fires).
     *
     * The listener type that attach() sets gives the handler shape. PHPStan
     * cannot follow that link, thus each branch declares the shape.
     *
     * @phpstan-param events_queue $queue
     */
    private function runQueue(
        array $queue,
        EventInterface $event,
        string $eventName,
        mixed $source,
        mixed $data,
        bool $cancelable,
        bool $collect,
        bool $stopOnFalse
    ): mixed {
        $status    = null;
        $queueSize = count($queue);

        // Single-handler fast path.
        if (1 === $queueSize) {
            $tuple   = $queue[0];
            $handler = $tuple[0];
            $type    = $tuple[1];

            if (0 === $type) {
                /** @phpstan-var Closure $handler */
                $ret = $handler($event, $source, $data);
            } elseif (1 === $type) {
                /** @phpstan-var array{0: object, 1: string} $handler */
                $ret = $handler[0]->{$handler[1]}($event, $source, $data);
            } elseif (2 === $type) {
                /**
                 * @phpstan-var object                                     $handler
                 * @phpstan-var array{0: object, 1: int, 2: int, 3: string} $tuple
                 */
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
                /** @phpstan-var callable $handler */
                $ret = call_user_func_array($handler, [$event, $source, $data]);
            }

            if ($collect) {
                $this->responses[] = $ret;
            }

            if ($stopOnFalse && $cancelable && false === $ret) {
                return false;
            }

            return $ret;
        }

        foreach ($queue as $tuple) {
            $handler = $tuple[0];
            $type    = $tuple[1];

            // Closure: direct invocation via Zephir's `{var}(...)`
            // callable-invocation syntax. Routes through PHP's normal
            // closure call path so arity mismatch is tolerated, unlike
            // `handler->__invoke(...)` which uses a strict C call path
            // that segfaults on mismatch.
            if (0 === $type) {
                /** @phpstan-var Closure $handler */
                $ret = $handler($event, $source, $data);
            } elseif (1 === $type) {
                /** @phpstan-var array{0: object, 1: string} $handler */
                $ret = $handler[0]->{$handler[1]}($event, $source, $data);
            } elseif (2 === $type) {
                /**
                 * @phpstan-var object                                     $handler
                 * @phpstan-var array{0: object, 1: int, 2: int, 3: string} $tuple
                 */
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
                /** @phpstan-var callable $handler */
                $ret = call_user_func_array($handler, [$event, $source, $data]);
            }

            if ($collect) {
                $this->responses[] = $ret;
            }

            // Opt-in hard `false`-cancel: when setStopOnFalse(true) has
            // been called, a listener returning false short-circuits
            // the queue and pins the dispatch return as false. fire()
            // checks the return and propagates accordingly.
            if ($stopOnFalse && $cancelable && false === $ret) {
                return false;
            }

            // stop() determinism: if the listener stopped the event,
            // its return is the dispatch result (even if null) and the
            // queue is abandoned.
            if ($cancelable && $event->isStopped()) {
                return $ret;
            }

            // Last non-null wins.
            if (null !== $ret) {
                $status = $ret;
            }
        }

        return $status;
    }
}
