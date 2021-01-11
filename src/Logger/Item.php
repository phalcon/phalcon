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

namespace Phalcon\Logger;

use DateTimeImmutable;

/**
 * Phalcon\Logger\Item
 *
 * Represents each item in a logging transaction
 *
 * @property array             $context
 * @property string            $message
 * @property string            $name
 * @property DateTimeImmutable $datetime
 * @property int               $type
 */
class Item
{
    /**
     * @var array
     */
    protected array $context = [];

    /**
     * @var string
     */
    protected string $message;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var DateTimeImmutable
     */
    protected DateTimeImmutable $datetime;

    /**
     * @var int
     */
    protected int $type;

    /**
     * Item constructor.
     *
     * @param string            $message
     * @param string            $name
     * @param int               $type
     * @param DateTimeImmutable $datetime
     * @param array             $context
     */
    public function __construct(
        string $message,
        string $name,
        int $type,
        DateTimeImmutable $datetime,
        array $context = []
    ) {
        $this->message  = $message;
        $this->name     = $name;
        $this->type     = $type;
        $this->datetime = $datetime;
        $this->context  = $context;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateTime(): DateTimeImmutable
    {
        return $this->datetime;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Alias of getDateTime
     *
     * @return DateTimeImmutable
     * @deprecated To be removed in v6
     */
    public function getTime(): DateTimeImmutable
    {
        return $this->getDateTime();
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}
