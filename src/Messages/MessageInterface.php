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

/**
 * Interface MessageInterface
 *
 * Interface for Phalcon\Messages\MessageInterface
 */
interface MessageInterface
{
    /**
     * Magic __toString method returns verbose message
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Returns the message code related to this message
     *
     * @return int
     */
    public function getCode(): int;

    /**
     * Returns field name related to message
     *
     * @return string
     */
    public function getField(): string;

    /**
     * Returns verbose message
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Returns message metadata
     *
     * @return array
     */
    public function getMetaData(): array;

    /**
     * Returns message type
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Sets code for the message
     *
     * @param int $code
     *
     * @return MessageInterface
     */
    public function setCode(int $code): MessageInterface;

    /**
     * Sets field name related to message
     *
     * @param string $field
     *
     * @return MessageInterface
     */
    public function setField(string $field): MessageInterface;

    /**
     * Sets verbose message
     *
     * @param string $message
     *
     * @return MessageInterface
     */
    public function setMessage(string $message): MessageInterface;

    /**
     * Sets message metadata
     *
     * @param array $metaData
     *
     * @return MessageInterface
     */
    public function setMetaData(array $metaData): MessageInterface;

    /**
     * Sets message type
     *
     * @param string $type
     *
     * @return MessageInterface
     */
    public function setType(string $type): MessageInterface;
}
