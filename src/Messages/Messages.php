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

use Iterator;
use JsonSerializable;
use Phalcon\Contracts\Messages\Messages as MessagesContract;
use Phalcon\Contracts\Messages\MessagesTypes;
use Phalcon\Messages\Exceptions\MessagesNotIterable;
use Phalcon\Messages\Traits\MessagesHelperTrait;
use Traversable;

use function array_merge;
use function is_array;
use function is_object;
use function method_exists;

/**
 * Represents a collection of messages
 *
 * Messages are stored and iterated by integer position. An entry added under a
 * string key through the ArrayAccess interface (for example
 * `$messages["database"] = $message`) stays reachable by that offset but is not
 * visited during iteration (`foreach`), which walks the integer sequence only.
 * Use the append methods (`appendMessage()` / `appendMessages()`) when entries
 * must take part in iteration.
 *
 * @phpstan-import-type messages_list from MessagesTypes
 * @phpstan-import-type messages_serialized from MessagesTypes
 */
class Messages implements MessagesContract, JsonSerializable
{
    use MessagesHelperTrait;

    /**
     * Phalcon\Messages\Messages constructor
     *
     * @param messages_list $messages
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
     * Accepts an array of MessageInterface objects or an Iterator yielding
     * them. The parameter stays untyped so that a non-iterable argument
     * reaches the guard below and raises MessagesNotIterable rather than a
     * TypeError.
     *
     * @param mixed $messages
     *
     * @return void
     * @throws MessagesNotIterable
     */
    public function appendMessages($messages)
    {
        if (!is_array($messages) && !($messages instanceof Traversable)) {
            throw new MessagesNotIterable();
        }

        $currentMessages = $this->messages;

        if (is_array($messages)) {
            /**
             * An array of messages is simply merged into the current one
             */
            /** @var messages_list $messages */
            $finalMessages = array_merge($currentMessages, $messages);

            $this->messages = $finalMessages;
        } else {
            /**
             * A collection of messages is iterated and appended one-by-one to
             * the current list
             */
            /** @var Iterator<array-key, MessageInterface> $messages */
            $messages->rewind();

            while ($messages->valid()) {
                $message = $messages->current();
                $this->appendMessage($message);
                $messages->next();
            }
        }
    }

    /**
     * Filters the message collection by field name
     *
     * @return messages_list
     */
    public function filter(string $fieldName): array
    {
        $filtered = [];

        /**
         * A collection of messages is iterated and appended one-by-one to
         * the current list
         */
        foreach ($this->messages as $message) {
            /**
             * The constructor accepts any array, so an entry is not guaranteed
             * to be a message; the guard keeps malformed entries from fataling.
             *
             * @var object $message
             */
            if (
                method_exists($message, 'getField')
                && $fieldName === $message->getField()
            ) {
                /** @var MessageInterface $message */
                $filtered[] = $message;
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
     * @return messages_serialized
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
     * @param mixed $value
     */
    private function checkSerializable(mixed $value): mixed
    {
        if (
            is_object($value) &&
            true === method_exists($value, 'jsonSerialize')
        ) {
            return $value->jsonSerialize();
        }

        return $value;
    }
}
