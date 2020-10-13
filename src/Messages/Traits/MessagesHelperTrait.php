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

namespace Phalcon\Messages\Traits;

use Phalcon\Messages\Exception;
use Phalcon\Messages\Message;
use Phalcon\Messages\MessageInterface;

/**
 * Trait MessagesHelperTrait
 *
 * @package Phalcon\Messages\Traits
 *
 * @property array $messages
 * @property int   $position
 */
trait MessagesHelperTrait
{
    /**
     * @var array
     */
    protected array $messages = [];

    /**
     * @var int
     */
    protected int $position = 0;

    /**
     * Phalcon\Messages\Messages constructor
     *
     * @param array $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * Returns the number of messages in the list
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * Returns the current message in the iterator
     *
     * @return MessageInterface
     */
    public function current(): MessageInterface
    {
        return $this->messages[$this->position];
    }

    /**
     * Returns the current position/key in the iterator
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the internal iteration pointer to the next position
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Checks if an index exists
     *
     *```php
     * var_dump(
     *     isset($message["database"])
     * );
     *```
     *
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->messages[$offset]);
    }

    /**
     * Gets an attribute a message using the array syntax
     *
     *```php
     * print_r(
     *     $messages[0]
     * );
     *```
     *
     * @param mixed $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->messages[$offset] ?? null;
    }

    /**
     * Sets an attribute using the array-syntax
     *
     *```php
     * $messages[0] = new \Phalcon\Messages\Message("This is a message");
     *```
     *
     * @param mixed   $offset
     * @param Message $message
     *
     * @throws Exception
     */
    public function offsetSet($offset, $message): void
    {
        if (true !== is_object($message)) {
            throw new Exception('The message must be an object');
        }

        $this->messages[$offset] = $message;
    }

    /**
     * Removes a message from the list
     *
     *```php
     * unset($message["database"]);
     *```
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        if (isset($this->messages[$offset])) {
            array_splice($this->messages, $offset, 1);
        }
    }

    /**
     * Rewinds the internal iterator
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Check if the current message in the iterator is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->messages[$this->position]);
    }
}
