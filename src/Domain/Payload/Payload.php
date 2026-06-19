<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
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

namespace Phalcon\Domain\Payload;

use Throwable;

/**
 * Holds the payload
 */
class Payload implements PayloadInterface
{
    /**
     * Exception if any
     *
     * @var Throwable|null
     */
    protected ?Throwable $exception = null;

    /**
     * Extra information
     *
     * @var mixed
     */
    protected mixed $extras = null;

    /**
     * Input
     *
     * @var mixed
     */
    protected mixed $input = null;

    /**
     * Messages
     *
     * @var mixed
     */
    protected mixed $messages = null;

    /**
     * Output
     *
     * @var mixed
     */
    protected mixed $output = null;

    /**
     * Status
     *
     * @var mixed
     */
    protected mixed $status = null;

    /**
     * Gets the potential exception thrown in the domain layer
     *
     * @return Throwable|null
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * Extra information
     *
     * @return mixed
     */
    public function getExtras(): mixed
    {
        return $this->extras;
    }

    /**
     * Input
     *
     * @return mixed
     */
    public function getInput(): mixed
    {
        return $this->input;
    }

    /**
     * Messages
     *
     * @return mixed
     */
    public function getMessages(): mixed
    {
        return $this->messages;
    }

    /**
     * Output
     *
     * @return mixed
     */
    public function getOutput(): mixed
    {
        return $this->output;
    }

    /**
     * Status
     *
     * Status values are drawn from the `Status` vocabulary.
     *
     * @return mixed
     *
     * @see Status
     */
    public function getStatus(): mixed
    {
        return $this->status;
    }

    /**
     * Sets an exception thrown in the domain
     *
     * @param Throwable $exception
     *
     * @return PayloadInterface
     */
    public function setException(Throwable $exception): PayloadInterface
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Sets arbitrary extra domain information.
     *
     * @param mixed $extras
     *
     * @return PayloadInterface
     */
    public function setExtras(mixed $extras): PayloadInterface
    {
        $this->extras = $extras;

        return $this;
    }

    /**
     * Sets the domain input.
     *
     * @param mixed $input
     *
     * @return PayloadInterface
     */
    public function setInput(mixed $input): PayloadInterface
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Sets the domain messages.
     *
     * @param mixed $messages
     *
     * @return PayloadInterface
     */
    public function setMessages(mixed $messages): PayloadInterface
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Sets the domain output.
     *
     * @param mixed $output
     *
     * @return PayloadInterface
     */
    public function setOutput(mixed $output): PayloadInterface
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Sets the payload status.
     *
     * Status values are drawn from the `Status` vocabulary.
     *
     * @param mixed $status
     *
     * @return PayloadInterface
     *
     * @see Status
     */
    public function setStatus(mixed $status): PayloadInterface
    {
        $this->status = $status;

        return $this;
    }
}
