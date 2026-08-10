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
use Stringable;

use function strtr;

/**
 * A memory-based logger.
 */
class MemoryLogger implements LoggerInterface
{
    protected array $messages = [];

    public function alert(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::ALERT, $message, $context);
    }

    public function critical(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::CRITICAL, $message, $context);
    }

    public function debug(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::DEBUG, $message, $context);
    }

    public function emergency(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::EMERGENCY, $message, $context);
    }

    public function error(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::ERROR, $message, $context);
    }

    /**
     * Returns an adapter from the stack
     */
    public function getAdapter(string $name): AdapterInterface
    {
        return new Noop();
    }

    /**
     * Returns the adapter stack array
     */
    public function getAdapters(): array
    {
        return [];
    }

    /**
     * Returns the log level
     */
    public function getLogLevel(): int
    {
        return Enum::CUSTOM;
    }

    /**
     * Returns the logged messages.
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

    public function info(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::INFO, $message, $context);
    }

    /**
     * Logs a message.
     */
    public function log(mixed $level, string | Stringable $message, array $context = []): void
    {
        $replace = [];

        foreach ($context as $key => $value) {
            $replace["{" . $key . "}"] = $value;
        }

        $this->messages[] = strtr((string)$message, $replace);
    }

    public function notice(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::NOTICE, $message, $context);
    }

    public function trace(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::TRACE, $message, $context);
    }

    public function warning(string | Stringable $message, array $context = []): void
    {
        $this->log(Enum::WARNING, $message, $context);
    }
}
