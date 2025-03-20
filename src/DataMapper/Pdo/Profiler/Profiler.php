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

use InvalidArgumentException;
use Phalcon\DataMapper\Pdo\Exception\Exception;
use Phalcon\Logger\Enum;
use Phalcon\Traits\Php\JsonTrait;
use Psr\Log\LoggerInterface;

use function json_encode;

use const PHP_EOL;

/**
 * Sends query profiles to a logger.
 */
class Profiler implements ProfilerInterface
{
    use JsonTrait;

    /**
     * @var array<array-key, mixed>
     */
    protected array $context = [];

    /**
     * @var bool
     */
    protected bool $isActive = false;

    /**
     * @var string
     */
    protected string $logFormat = "M: {method} ({duration}s)"
    . PHP_EOL
    . "S: {statement}"
    . PHP_EOL
    . "V: {values}"
    . PHP_EOL
    . "B: {backtrace}";

    /**
     * @var int
     */
    protected int $logLevel = Enum::DEBUG;

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        protected LoggerInterface | null $logger = null
    ) {
    }

    /**
     *
     * Finishes and logs a profile entry.
     *
     * @param string|null           $statement
     * @param array<string, string> $values
     *
     * @return void
     */
    public function finish(string | null $statement = null, array $values = []): void
    {
        if (true === $this->isActive) {
            $ex     = new Exception();
            $finish = hrtime(true);

            $this->context["backtrace"] = '';//$ex->getTraceAsString();
            $this->context["duration"]  = ($finish - $this->context["start"]) / 1e+9;
            $this->context["finish"]    = $finish;
            $this->context["statement"] = $statement;
            $this->context["values"]    = empty($values)
                ? ""
                : $this->encode($values);

            $this->logger?->log(
                $this->logLevel,
                $this->logFormat,
                $this->context
            );

            $this->context = [];
        }
    }

    /**
     * Returns the log message format string, with placeholders.
     *
     * @return string
     */
    public function getLogFormat(): string
    {
        return $this->logFormat;
    }

    /**
     * Returns the level at which to log profile messages.
     *
     * @return int
     */
    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    /**
     * Returns the underlying logger instance.
     *
     * @return LoggerInterface|null
     */
    public function getLogger(): LoggerInterface | null
    {
        return $this->logger;
    }

    /**
     * Returns true if logging is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Enable or disable profiler logging.
     *
     * @param bool $active
     *
     * @return ProfilerInterface
     */
    public function setActive(bool $active): ProfilerInterface
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * Sets the log message format string, with placeholders.
     *
     * @param string $logFormat
     *
     * @return ProfilerInterface
     */
    public function setLogFormat(string $logFormat): ProfilerInterface
    {
        $this->logFormat = $logFormat;

        return $this;
    }

    /**
     * Level at which to log profile messages.
     *
     * @param int $logLevel
     *
     * @return ProfilerInterface
     */
    public function setLogLevel(int $logLevel): ProfilerInterface
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    /**
     * Starts a profile entry.
     *
     * @param string $method
     */
    public function start(string $method): void
    {
        if (true === $this->isActive) {
            $this->context = [
                "method" => $method,
                "start"  => hrtime(true),
            ];
        }
    }

    /**
     * @param mixed $data
     * @param int   $options
     * @param int   $depth
     *
     * @return string
     * @todo This will be removed when traits are introduced
     *
     */
    private function encode(
        mixed $data,
        int $options = 0,
        int $depth = 512
    ): string {
        /**
         * Clear any errors
         */
        json_encode(null);
        $encoded = $this->phpJsonEncode($data, $options, $depth);

        if (false === $encoded) {
            throw new InvalidArgumentException(
                "json_encode error: " . json_last_error_msg()
            );
        }

        return $encoded;
    }
}
