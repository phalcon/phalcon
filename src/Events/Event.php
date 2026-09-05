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

use Phalcon\Contracts\Events\Stoppable;
use Phalcon\Events\Exceptions\EventNotCancelable;

/**
 * Phalcon\Events\Event
 *
 * This class offers contextual information of a fired event in the
 * EventsManager
 *
 *```php
 * Phalcon\Events\Event;
 *
 * $event = new Event("db:afterQuery", $this, ["data" => "mydata"], true);
 * if ($event->isCancelable()) {
 *     $event->stop();
 * }
 * ```
 */
class Event implements EventInterface, Stoppable
{
    /**
     * Is event propagation stopped?
     */
    protected bool $stopped = false;

    /**
     * Event constructor.
     */
    public function __construct(
        protected string $type,
        protected object | null $source = null,
        protected mixed $data = null,
        protected bool $cancelable = true
    ) {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getSource(): object | null
    {
        return $this->source;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Check whether the event is cancelable.
     *
     * ```php
     * if ($event->isCancelable()) {
     *     $event->stop();
     * }
     * ```
     */
    public function isCancelable(): bool
    {
        return $this->cancelable;
    }

    /**
     * Returns whether propagation must stop. PSR-14 alias backed by the same
     * `stopped` flag as `isStopped()`; calling `stop()` flips both.
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * Check whether the event is currently stopped.
     */
    public function isStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * Sets event data.
     */
    public function setData(mixed $data = null): EventInterface
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets event type.
     */
    public function setType(string $type): EventInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Stops the event preventing propagation.
     *
     * ```php
     * if ($event->isCancelable()) {
     *     $event->stop();
     * }
     * ```
     *
     * @throws EventNotCancelable
     */
    public function stop(): EventInterface
    {
        if (true !== $this->cancelable) {
            throw new EventNotCancelable();
        }

        $this->stopped = true;

        return $this;
    }
}
