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

use JsonSerializable;

/**
 * Class Message
 *
 * Stores a message from various components
 */
class Message implements MessageInterface, JsonSerializable
{
    /**
     * Phalcon\Messages\Message constructor
     *
     * @param string $message
     * @param string $field
     * @param string $type
     * @param int    $code
     * @param array  $metaData
     */
    public function __construct(
        protected string $message,
        protected string $field = "",
        protected string $type = "",
        protected int $code = 0,
        protected array $metaData = []
    ) {
    }

    /**
     * Magic __toString method returns verbose message
     */
    public function __toString(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Serializes the object for json_encode
     *
     * @return array
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize(): array
    {
        return [
            "field"    => $this->field,
            "message"  => $this->message,
            "type"     => $this->type,
            "code"     => $this->code,
            "metaData" => $this->metaData,
        ];
    }

    /**
     * Sets code for the message
     *
     * @param int $code
     *
     * @return MessageInterface
     */
    public function setCode(int $code): MessageInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Sets field name related to message
     *
     * @param string $field
     *
     * @return MessageInterface
     */
    public function setField(string $field): MessageInterface
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Sets verbose message
     *
     * @param string $message
     *
     * @return MessageInterface
     */
    public function setMessage(string $message): MessageInterface
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Sets message metadata
     *
     * @param array $metaData
     *
     * @return MessageInterface
     */
    public function setMetaData(array $metaData): MessageInterface
    {
        $this->metaData = $metaData;

        return $this;
    }

    /**
     * Sets message type
     *
     * @param string $type
     *
     * @return MessageInterface
     */
    public function setType(string $type): MessageInterface
    {
        $this->type = $type;

        return $this;
    }
}
