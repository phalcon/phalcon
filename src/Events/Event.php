<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Events;

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
 *
 * @property bool   $cancelable
 * @property mixed  $data
 * @property object $source
 * @property bool   $stopped
 * @property string $type
 */
class Event implements EventInterface
{
    /**
     * Is event cancelable?
     *
     * @var bool
     */
    protected bool $cancelable;

    /**
     * Event data
     *
     * @var mixed
     */
    protected $data;

    /**
     * Event source
     *
     * @var object
     */
    protected object $source;

    /**
     * Is event propagation stopped?
     *
     * @var bool
     */
    protected bool $stopped = false;

    /**
     * Event type
     *
     * @var string
     */
    protected string $type;

    /**
     * Event constructor.
     *
     * @param string     $type
     * @param object     $source
     * @param mixed|null $data
     * @param bool       $cancelable
     */
    public function __construct(
        string $type,
        object $source,
        $data = null,
        bool $cancelable = true
    ) {
        $this->type       = $type;
        $this->source     = $source;
        $this->data       = $data;
        $this->cancelable = $cancelable;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return object
     */
    public function getSource(): object
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
     */
    public function isCancelable(): bool
    {
        return $this->cancelable;
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
     *
     * @param mixed|null $data
     *
     * @return EventInterface
     */
    public function setData($data = null): EventInterface
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
     * @throws Exception
     */
    public function stop(): EventInterface
    {
        if (true !== $this->cancelable)  {
            throw new Exception('Trying to cancel a non-cancelable event');
        }

        $this->stopped = true;

        return $this;
    }
}
