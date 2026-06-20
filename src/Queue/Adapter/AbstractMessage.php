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

use function array_key_exists;

/**
 * Shared implementation of every Message getter/setter, plus the
 * correlation-id / message-id / timestamp / reply-to header conveniences.
 * Concrete adapter messages extend this base.
 *
 * The convenience accessors are stored as transport headers under fixed keys
 * for binary compatibility with the wider interop ecosystem.
 */
abstract class AbstractMessage implements MessageInterface
{
    protected string $body = "";
    protected array $headers = [];
    protected array $properties = [];
    protected bool $redelivered = false;

    public function __construct(string $body = "", array $properties = [], array $headers = [])
    {
        $this->body       = $body;
        $this->properties = $properties;
        $this->headers    = $headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getCorrelationId(): ?string
    {
        return $this->getHeader("correlation_id");
    }

    public function getHeader(string $name, mixed $defaultValue = null): mixed
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }

        return $defaultValue;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getMessageId(): ?string
    {
        return $this->getHeader("message_id");
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getProperty(string $name, mixed $defaultValue = null): mixed
    {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        return $defaultValue;
    }

    public function getReplyTo(): ?string
    {
        return $this->getHeader("reply_to");
    }

    public function getTimestamp(): ?int
    {
        $value = $this->getHeader("timestamp");

        if ($value === null) {
            return null;
        }

        return (int) $value;
    }

    public function isRedelivered(): bool
    {
        return $this->redelivered;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function setCorrelationId(string $correlationId): void
    {
        $this->setHeader("correlation_id", $correlationId);
    }

    public function setHeader(string $name, mixed $value): void
    {
        $this->headers[$name] = $value;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function setMessageId(string $messageId): void
    {
        $this->setHeader("message_id", $messageId);
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function setProperty(string $name, mixed $value): void
    {
        $this->properties[$name] = $value;
    }

    public function setRedelivered(bool $redelivered): void
    {
        $this->redelivered = $redelivered;
    }

    public function setReplyTo(string $replyTo): void
    {
        $this->setHeader("reply_to", $replyTo);
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->setHeader("timestamp", $timestamp);
    }
}
