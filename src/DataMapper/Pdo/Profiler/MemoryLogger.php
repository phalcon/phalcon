<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Pdo\Profiler;

use Phalcon\Logger\Adapter\AdapterInterface;
use Phalcon\Logger\Adapter\Noop;
use Phalcon\Logger\Enum;
use Phalcon\Logger\LoggerInterface;

/**
 *
 * A naive memory-based logger.
 *
 * @package Aura.Sql
 *
 */
class MemoryLogger implements LoggerInterface
{
    /**
     * @var array
     */
    protected array $messages = [];

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(Enum::ALERT, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(Enum::CRITICAL, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(Enum::DEBUG, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(Enum::EMERGENCY, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(Enum::ERROR, $message, $context);
    }

    /**
     * Returns an adapter from the stack
     *
     * @param string $name The name of the adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter(string $name): AdapterInterface
    {
        return new Noop();
    }

    /**
     * Returns the adapter stack array
     *
     * @return AdapterInterface[]
     */
    public function getAdapters(): array
    {
        return [];
    }

    /**
     * Returns the log level
     *
     * @return int
     */
    public function getLogLevel(): int
    {
        return Enum::CUSTOM;
    }

    /**
     * Returns the logged messages.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Returns the name of the logger
     */
    public function getName(): string
    {
        return "memory logger";
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(Enum::INFO, $message, $context);
    }

    /**
     * Logs a message.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log(mixed $level, string $message, array $context = []): void
    {
        $replace = [];
        foreach ($context as $key => $item) {
            $replace["{" . $key . "}"] = $item;
        }

        $this->messages[] = strtr($message, $replace);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(Enum::NOTICE, $message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(Enum::WARNING, $message, $context);
    }
}
