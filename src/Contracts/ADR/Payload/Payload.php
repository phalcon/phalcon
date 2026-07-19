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

namespace Phalcon\Contracts\ADR\Payload;

use Throwable;

/**
 * Contract for the immutable payload produced by the domain layer.
 */
interface Payload
{
    /**
     * Gets the exception thrown in the domain layer, if any.
     */
    public function getException(): ?Throwable;

    /**
     * Gets the arbitrary extra domain information.
     */
    public function getExtras(): mixed;

    /**
     * Gets the domain input.
     */
    public function getInput(): mixed;

    /**
     * Gets the domain messages.
     */
    public function getMessages(): mixed;

    /**
     * Gets the domain result.
     */
    public function getResult(): mixed;

    /**
     * Gets the payload status.
     */
    public function getStatus(): mixed;

    /**
     * Returns a copy of the payload with the given exception.
     */
    public function withException(Throwable $exception): Payload;

    /**
     * Returns a copy of the payload with the given extras.
     */
    public function withExtras(mixed $extras): Payload;

    /**
     * Returns a copy of the payload with the given input.
     */
    public function withInput(mixed $input): Payload;

    /**
     * Returns a copy of the payload with the given messages.
     */
    public function withMessages(mixed $messages): Payload;

    /**
     * Returns a copy of the payload with the given result.
     */
    public function withResult(mixed $result): Payload;

    /**
     * Returns a copy of the payload with the given status.
     */
    public function withStatus(mixed $status): Payload;
}
