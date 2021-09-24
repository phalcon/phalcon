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
 * @property int               $level
 * @property string            $levelName
 * @property DateTimeImmutable $datetime
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
     * @var int
     */
    protected int $level;

    /**
     * @var string
     */
    protected string $levelName;

    /**
     * @var DateTimeImmutable
     */
    protected DateTimeImmutable $datetime;

    /**
     * Item constructor.
     *
     * @param string            $message
     * @param string            $levelName
     * @param int               $level
     * @param DateTimeImmutable $datetime
     * @param array             $context
     */
    public function __construct(
        string $message,
        string $levelName,
        int $level,
        DateTimeImmutable $datetime,
        array $context = []
    ) {
        $this->message   = $message;
        $this->levelName = $levelName;
        $this->level     = $level;
        $this->datetime  = $datetime;
        $this->context   = $context;
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
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getLevelName(): string
    {
        return $this->levelName;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
