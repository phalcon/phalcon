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

use Phalcon\Contracts\DataMapper\DataMapperTypes;
use Phalcon\DataMapper\Pdo\Exception\Exception;
use Phalcon\Logger\Enum;
use Phalcon\Logger\LoggerInterface;
use Phalcon\Support\Helper\Json\Encode;

use function hrtime;

/**
 * Sends query profiles to a logger.
 *
 * @phpstan-import-type datamapper_profiler_context from DataMapperTypes
 * @phpstan-import-type datamapper_values from DataMapperTypes
 */
class Profiler implements ProfilerInterface
{
    protected bool $active = false;
    /**
     * @phpstan-var datamapper_profiler_context
     */
    protected array $context = [];
    protected string $logFormat = "";
    protected LoggerInterface $logger;
    protected int | string $logLevel = 0;
    private Encode $encode;

    /**
     * Constructor.
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new MemoryLogger();
        }

        $this->logFormat = "{method} ({duration}s): {statement} {backtrace}";
        $this->logLevel  = Enum::DEBUG;
        $this->logger    = $logger;
        $this->encode    = new Encode();
    }

    /**
     * Finishes and logs a profile entry.
     *
     * @phpstan-param datamapper_values $values
     */
    public function finish(?string $statement = null, array $values = []): void
    {
        if ($this->active) {
            $ex     = new Exception();
            $finish = hrtime(true);

            /** @phpstan-var float|int $start */
            $start = $this->context["start"];

            $this->context["backtrace"] = $ex->getTraceAsString();
            $this->context["duration"]  = $finish - $start;
            $this->context["finish"]    = $finish;
            $this->context["statement"] = $statement;
            $this->context["values"]    = empty($values)
                ? ""
                : $this->encode->__invoke($values);

            $this->logger->log($this->logLevel, $this->logFormat, $this->context);

            $this->context = [];
        }
    }

    /**
     * Returns the log message format string, with placeholders.
     */
    public function getLogFormat(): string
    {
        return $this->logFormat;
    }

    /**
     * Returns the underlying logger instance.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Returns the level at which to log profile messages.
     */
    public function getLogLevel(): string
    {
        return (string)$this->logLevel;
    }

    /**
     * Returns true if logging is active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Enable or disable profiler logging.
     */
    public function setActive(bool $active): ProfilerInterface
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Sets the log message format string, with placeholders.
     */
    public function setLogFormat(string $logFormat): ProfilerInterface
    {
        $this->logFormat = $logFormat;

        return $this;
    }

    /**
     * Level at which to log profile messages.
     */
    public function setLogLevel(string $logLevel): ProfilerInterface
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    /**
     * Starts a profile entry.
     */
    public function start(string $method): void
    {
        if ($this->active) {
            $this->context = [
                "method" => $method,
                "start"  => hrtime(true),
            ];
        }
    }
}
