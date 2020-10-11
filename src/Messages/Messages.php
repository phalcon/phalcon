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

namespace Phalcon\Messages;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use Phalcon\Support\Traits\JsonTrait;
use function is_array;

/**
 * Class Messages
 *
 * Represents a collection of messages
 *
 * @property array $messages
 * @property int   $position
 */
class Messages implements ArrayAccess, Countable, Iterator, JsonSerializable
{
    use JsonTrait;

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
     * Appends a message to the collection
     *
     *```php
     * $messages->appendMessage(
     *     new \Phalcon\Messages\Message("This is a message")
     * );
     *```
     * @param MessageInterface $message
     */
    public function appendMessage(MessageInterface $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * Appends an array of messages to the collection
     *
     *```php
     * $messages->appendMessages($messagesArray);
     *```
     *
     * @param MessageInterface[]|Iterator $messages
     * @throws Exception
     */
    public function appendMessages($messages): void
    {
        if (true !== is_iterable($messages)) {
            throw new Exception("The messages must be iterable");
        }

        $this->checkAppendMessagesArray($messages);
        $this->checkAppendMessages($messages);
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
     * Filters the message collection by field name
     *
     * @param string $fieldName
     * @return array
     */
    public function filter(string $fieldName): array
    {
        $filtered = [];

        if (is_array($this->messages)) {
            /**
             * A collection of messages is iterated and appended one-by-one to
             * the current list
             */
            foreach ($this->messages as $message) {
                if (
                    method_exists($message, 'getField')
                    && $fieldName === $message->getField()
                ) {
                    $filtered[] = $message;
                }
            }
        }

        return $filtered;
    }

    /**
     * Returns serialised message objects as array for json_encode. Calls
     * jsonSerialize on each object if present
     *
     *```php
     * $data = $messages->jsonSerialize();
     * echo json_encode($data);
     *```
     *
     * @return array
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize(): array
    {
        $records = [];

        foreach ($this->messages as $message) {
            $records[] = $this->checkSerializable($message);
        }

        return $records;
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
     * @param mixed $offset
     * @param Message $message
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

    /**
     * @param mixed $messages
     */
    private function checkAppendMessages($messages): void
    {
        if (true === is_array($messages)) {
            /**
             * An array of messages is simply merged into the current one
             */

            $this->messages = [...$this->messages, ...$messages];
        }
    }

    /**
     * @param mixed $append
     */
    private function checkAppendMessagesArray($messages): void
    {
        if (true !== is_array($messages)) {
            /**
             * A collection of messages is iterated and appended one-by-one to
             * the current list
             */

            $messages->rewind();

            while ($messages->valid()) {
                $message = $messages->current();
                $this->appendMessage($message);
                $messages->next();
            }
        }

    }
}
