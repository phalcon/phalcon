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

use Phalcon\Logger\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Stringable;

/**
 * Interface for Phalcon based logger objects.
 * Extends PSR-3 LoggerInterface for compatibility.
 */
interface LoggerInterface extends PsrLoggerInterface
{
    /**
     * Returns an adapter from the stack
     *
     * @param string $name The name of the adapter
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function getAdapter(string $name): AdapterInterface;

    /**
     * Returns the adapter stack array
     *
     * @return AdapterInterface[]
     */
    public function getAdapters(): array;

    /**
     * Returns the log level
     *
     * @return int
     */
    public function getLogLevel(): int;

    /**
     * Returns the name of the logger
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Extra-verbose diagnostic output.
     *
     * @param string|Stringable $message
     * @param array             $context
     *
     * @return void
     */
    public function trace(string | Stringable $message, array $context = []): void;
}
