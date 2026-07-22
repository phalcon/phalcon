<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Based on the Action Domain Responder pattern
 * @link    https://pmjones.io/adr/
 *
 * Implementation of this file has been influenced by phalcon-api and AuraPHP
 * @link    https://github.com/phalcon/phalcon-api
 * @license https://github.com/phalcon/phalcon-api/blob/master/LICENSE
 * @link    https://github.com/auraphp/Aura.Payload
 * @license https://github.com/auraphp/Aura.Payload/blob/3.x/LICENSE
 *
 * @see Original inspiration for the https://github.com/phalcon/phalcon-api
 */

declare(strict_types=1);

namespace Phalcon\ADR\Payload;

use Phalcon\Contracts\ADR\Payload\Payload as PayloadContract;
use Throwable;

/**
 * Immutable payload produced by the domain layer.
 *
 * Every `with*()` method returns a new instance, leaving the receiver
 * unchanged. Named factories provide a concise way to create a payload for the
 * commonly used statuses.
 */
class Payload implements PayloadContract
{
    /**
     * @var Throwable|null
     */
    protected ?Throwable $exception = null;

    /**
     * @var mixed
     */
    protected mixed $extras = null;

    /**
     * @var mixed
     */
    protected mixed $input = null;

    /**
     * @var mixed
     */
    protected mixed $messages = null;

    /**
     * @var mixed
     */
    protected mixed $result = null;

    /**
     * @var mixed
     */
    protected mixed $status = null;

    /**
     * Creates a payload with the `ACCEPTED` status.
     */
    public static function accepted(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::ACCEPTED)->withResult($result);
    }

    /**
     * Creates a payload with the `AUTHENTICATED` status.
     */
    public static function authenticated(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::AUTHENTICATED)->withResult($result);
    }

    /**
     * Creates a payload with the `AUTHORIZED` status.
     */
    public static function authorized(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::AUTHORIZED)->withResult($result);
    }

    /**
     * Creates a payload with the `CREATED` status.
     */
    public static function created(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::CREATED)->withResult($result);
    }

    /**
     * Creates a payload with the `DELETED` status.
     */
    public static function deleted(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::DELETED)->withResult($result);
    }

    /**
     * Creates a payload with the `ERROR` status.
     */
    public static function error(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::ERROR)->withMessages($messages);
    }

    /**
     * Creates a payload with the `NOT_AUTHORIZED` status (authenticated but
     * not allowed - HTTP 403).
     */
    public static function forbidden(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_AUTHORIZED)->withMessages($messages);
    }

    /**
     * Creates a payload with the `FOUND` status.
     */
    public static function found(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::FOUND)->withResult($result);
    }

    /**
     * Creates a payload with the `NOT_VALID` status.
     */
    public static function invalid(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_VALID)->withMessages($messages);
    }

    /**
     * Creates a payload with the `NOT_ACCEPTED` status.
     */
    public static function notAccepted(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_ACCEPTED)->withMessages($messages);
    }

    /**
     * Creates a payload with the `NOT_CREATED` status.
     */
    public static function notCreated(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_CREATED)->withMessages($messages);
    }

    /**
     * Creates a payload with the `NOT_DELETED` status.
     */
    public static function notDeleted(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_DELETED)->withMessages($messages);
    }

    /**
     * Creates a payload with the `NOT_FOUND` status.
     */
    public static function notFound(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_FOUND)->withMessages($messages);
    }

    /**
     * Creates a payload with the `NOT_UPDATED` status.
     */
    public static function notUpdated(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_UPDATED)->withMessages($messages);
    }

    /**
     * Creates a payload with the `PROCESSING` status.
     */
    public static function processing(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::PROCESSING)->withResult($result);
    }

    /**
     * Creates a payload with the `SUCCESS` status.
     */
    public static function success(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::SUCCESS)->withResult($result);
    }

    /**
     * Creates a payload with the `NOT_AUTHENTICATED` status (identity not
     * established - HTTP 401).
     */
    public static function unauthenticated(mixed $messages = null): PayloadContract
    {
        return (new static())->withStatus(Status::NOT_AUTHENTICATED)->withMessages($messages);
    }

    /**
     * Creates a payload with the `UPDATED` status.
     */
    public static function updated(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::UPDATED)->withResult($result);
    }

    /**
     * Creates a payload with the `VALID` status.
     */
    public static function valid(mixed $result = null): PayloadContract
    {
        return (new static())->withStatus(Status::VALID)->withResult($result);
    }

    /**
     * Gets the exception thrown in the domain layer, if any.
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * Gets the arbitrary extra domain information.
     */
    public function getExtras(): mixed
    {
        return $this->extras;
    }

    /**
     * Gets the domain input.
     */
    public function getInput(): mixed
    {
        return $this->input;
    }

    /**
     * Gets the domain messages.
     */
    public function getMessages(): mixed
    {
        return $this->messages;
    }

    /**
     * Gets the domain result.
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Gets the payload status.
     */
    public function getStatus(): mixed
    {
        return $this->status;
    }

    /**
     * Returns a copy of the payload with the given exception.
     */
    public function withException(Throwable $exception): PayloadContract
    {
        $cloned            = clone $this;
        $cloned->exception = $exception;

        return $cloned;
    }

    /**
     * Returns a copy of the payload with the given extras.
     */
    public function withExtras(mixed $extras): PayloadContract
    {
        $cloned         = clone $this;
        $cloned->extras = $extras;

        return $cloned;
    }

    /**
     * Returns a copy of the payload with the given input.
     */
    public function withInput(mixed $input): PayloadContract
    {
        $cloned        = clone $this;
        $cloned->input = $input;

        return $cloned;
    }

    /**
     * Returns a copy of the payload with the given messages.
     */
    public function withMessages(mixed $messages): PayloadContract
    {
        $cloned           = clone $this;
        $cloned->messages = $messages;

        return $cloned;
    }

    /**
     * Returns a copy of the payload with the given result.
     */
    public function withResult(mixed $result): PayloadContract
    {
        $cloned         = clone $this;
        $cloned->result = $result;

        return $cloned;
    }

    /**
     * Returns a copy of the payload with the given status.
     */
    public function withStatus(mixed $status): PayloadContract
    {
        $cloned         = clone $this;
        $cloned->status = $status;

        return $cloned;
    }
}
