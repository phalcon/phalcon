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

/**
 * Phalcon\Logger\Item
 *
 * Represents each item in a logging transaction
 *
 * @property array  $context
 * @property string $message
 * @property string $name
 * @property int    $time
 * @property int    $type
 */
class Item
{
    /**
     * @var array
     */
    protected array $context;

    /**
     * @var string
     */
    protected string $message;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $time;

    /**
     * @var int
     */
    protected int $type;

    /**
     * Item constructor.
     *
     * @param string $message
     * @param string $name
     * @param int    $type
     * @param int    $time
     * @param array  $context
     */
    public function __construct(
        string $message,
        string $name,
        int $type,
        int $time = 0,
        array $context = []
    ) {
        $this->message = $message;
        $this->name    = $name;
        $this->type    = $type;
        $this->time    = $time;
        $this->context = $context;
    }

    /**
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
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
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}
