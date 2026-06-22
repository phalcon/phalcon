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
 * `allowed_classes => false` guard, and the missing-key defaults, so each
 * adapter only supplies its own concrete message factory around `decode()`.
 */
class MessageEnvelope
{
    /**
     * Decodes a serialized payload into a normalized {body, properties,
     * headers} array, or null when the payload is not a valid envelope.
     *
     * @return array{body: mixed, properties: array, headers: array}|null
     */
    public static function decode(string $payload): ?array
    {
        $data = unserialize($payload, ["allowed_classes" => false]);

        if (!is_array($data)) {
            return null;
        }

        $body       = "";
        $properties = [];
        $headers    = [];

        if (isset($data["body"])) {
            $body = $data["body"];
        }

        if (isset($data["properties"])) {
            $properties = $data["properties"];
        }

        if (isset($data["headers"])) {
            $headers = $data["headers"];
        }

        return [
            "body"       => $body,
            "properties" => $properties,
            "headers"    => $headers,
        ];
    }

    /**
     * Serializes a message into its wire envelope.
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
