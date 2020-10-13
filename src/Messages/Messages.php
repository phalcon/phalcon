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
use Phalcon\Messages\Traits\MessagesHelperTrait;
use Phalcon\Support\Traits\JsonTrait;

use function is_array;

/**
 * Class Messages
 *
 * Represents a collection of messages
 */
class Messages implements ArrayAccess, Countable, Iterator, JsonSerializable
{
    use JsonTrait;
    use MessagesHelperTrait;

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
     *
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
     *
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
     * Filters the message collection by field name
     *
     * @param string $fieldName
     *
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
