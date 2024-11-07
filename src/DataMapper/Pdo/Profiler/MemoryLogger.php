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
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

/**
 * A naive memory-based logger.
 */
class MemoryLogger implements LoggerInterface
{
    use LoggerTrait;

    /**
     * @var array<array-key, string>
     */
    protected array $messages = [];

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
     * @return array<array-key, string>
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
     * Logs a message.
     *
     * @param mixed                    $level
     * @param string|Stringable        $message
     * @param array<array-key, string> $context
     *
     * @return void
     */
    public function log(
        mixed $level,
        string | Stringable $message,
        array $context = []
    ): void {
        $replace = [];
        foreach ($context as $key => $item) {
            $replace["{" . $key . "}"] = $item;
        }

        $this->messages[] = strtr((string)$message, $replace);
    }
}
