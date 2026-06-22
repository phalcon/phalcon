<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this component has been inspired by the queue-interop and
 * enqueue projects.
 *
 * @link    https://github.com/queue-interop/queue-interop
 * @license https://github.com/queue-interop/queue-interop/blob/master/LICENSE
 *
 * @link    https://github.com/php-enqueue/enqueue-dev
 * @license https://github.com/php-enqueue/enqueue-dev/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Queue\Adapter;

use Phalcon\Contracts\Queue\Message as MessageInterface;

use function is_array;
use function serialize;
use function unserialize;

/**
 * Encodes and decodes the {body, properties, headers} envelope shared by every
 * transport that persists a message as a serialized string (Stream, Redis,
 * Beanstalk). Centralizes the wire shape, the object-injection-safe
 * `allowed_classes => false` guard, and the missing-key defaults so each
 * adapter only supplies its own concrete message factory.
 */
final class MessageEnvelope
{
    /**
     * Decodes a payload into a message via the supplied factory, or null when
     * the payload is not a valid envelope.
     *
     * @param callable(string, array, array): MessageInterface $factory
     */
    public static function decode(string $payload, callable $factory): ?MessageInterface
    {
        $data = unserialize($payload, ["allowed_classes" => false]);

        if (!is_array($data)) {
            return null;
        }

        return $factory(
            $data["body"] ?? "",
            $data["properties"] ?? [],
            $data["headers"] ?? []
        );
    }

    /**
     * Encodes a message into its serialized envelope.
     */
    public static function encode(MessageInterface $message): string
    {
        return serialize(
            [
                "body"       => $message->getBody(),
                "properties" => $message->getProperties(),
                "headers"    => $message->getHeaders(),
            ]
        );
    }
}
