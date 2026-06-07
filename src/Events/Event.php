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
     *
     * @var bool
     */
    protected bool $stopped = false;

    /**
     * Event constructor.
     *
     * @param string      $type
     * @param object|null $source
     * @param mixed|null  $data
     * @param bool        $cancelable
     */
    public function __construct(
        protected string $type,
        protected object | null $source = null,
        protected mixed $data = null,
        protected bool $cancelable = true
    ) {
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return object|null
     */
    public function getSource(): object | null
    {
        return $this->source;
    }

    /**
     * @return string
     */
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
     *
     * @return bool
     */
    public function isCancelable(): bool
    {
        return $this->cancelable;
    }

    /**
     * Returns whether propagation must stop. PSR-14 alias backed by the same
     * `stopped` flag as `isStopped()`; calling `stop()` flips both.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * Check whether the event is currently stopped.
     *
     * @return bool
     */
    public function isStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * Sets event data.
     *
     * @param mixed|null $data
     *
     * @return EventInterface
     */
    public function setData(mixed $data = null): EventInterface
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets event type.
     *
     * @param string $type
     *
     * @return EventInterface
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
     * @return EventInterface
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
