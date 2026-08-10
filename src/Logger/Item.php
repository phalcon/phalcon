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
use Phalcon\Contracts\Logger\LoggerTypes;

/**
 * Phalcon\Logger\Item
 *
 * Represents each item in a logging transaction
 *
 * @property array<string, mixed> $context
 * @property string            $message
 * @property int               $level
 * @property string            $levelName
 * @property DateTimeImmutable $dateTime
 *
 * @phpstan-import-type logger_context from LoggerTypes
 */
class Item
{
    /**
     * Item constructor.
     *
     * @param string            $message
     * @param string            $levelName
     * @param int               $level
     * @param DateTimeImmutable $dateTime
     * @phpstan-param logger_context $context
     */
    public function __construct(
        protected string $message,
        protected string $levelName,
        protected int $level,
        protected DateTimeImmutable $dateTime,
        protected array $context = []
    ) {
    }

    /**
     * @phpstan-return logger_context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getLevelName(): string
    {
        return $this->levelName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
