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
use DateTimeZone;
use Exception;
use Phalcon\Logger\Adapter\AdapterInterface;
use Phalcon\Logger\Exception as LoggerException;
use Psr\Log\LoggerInterface;

use function array_flip;
use function date_default_timezone_get;
use function is_numeric;
use function is_string;

/**
 * Class Logger
 *
 * @package Phalcon\Logger
 *
 * @property AdapterInterface[] $adapters
 * @property array              $excluded
 * @property int                $logLevel
 * @property string             $name
 * @property string             $timezone
 */
class Logger implements LoggerInterface
{
    public const ALERT     = 2;
    public const CRITICAL  = 1;
    public const CUSTOM    = 8;
    public const DEBUG     = 7;
    public const EMERGENCY = 0;
    public const ERROR     = 3;
    public const INFO      = 6;
    public const NOTICE    = 5;
    public const WARNING   = 4;

    /**
     * The adapter stack
     *
     * @var AdapterInterface[]
     */
    protected array $adapters = [];

    /**
     * The excluded adapters for this log process
     *
     * @var array
     */
    protected array $excluded = [];

    /**
     * Minimum log level for the logger
     *
     * @var int
     */
    protected int $logLevel = 8;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var DateTimeZone
     */
    protected DateTimeZone $timezone;

    /**
     * Constructor.
     *
     * @param string $name                The name of the logger
     * @param array  $adapters            The collection of adapters to be used for
     *                                    logging (default [])
     * @param DateTimeZone|null $timezone Timezone. If omitted,
     *                                    date_Default_timezone_get() is used
     */
    public function __construct(
        string $name,
        array $adapters = [],
        ?DateTimeZone $timezone = null
    ) {
        if (null == $timezone) {
            $timezone = new DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        $this->name     = $name;
        $this->timezone = $timezone;

        $this->setAdapters($adapters);
    }

    /**
     * Add an adapter to the stack. For processing we use FIFO
     *
     * @param string           $name    The name of the adapter
     * @param AdapterInterface $adapter The adapter to add to the stack
     *
     * @return Logger
     */
    public function addAdapter(string $name, AdapterInterface $adapter): Logger
    {
        $this->adapters[$name] = $adapter;

        return $this;
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function alert($message, array $context = []): void
    {
        $this->addMessage(self::ALERT, (string) $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function critical($message, array $context = []): void
    {
        $this->addMessage(self::CRITICAL, (string) $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function debug($message, array $context = []): void
    {
        $this->addMessage(self::DEBUG, (string) $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function error($message, array $context = []): void
    {
        $this->addMessage(self::ERROR, (string) $message, $context);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function emergency($message, array $context = []): void
    {
        $this->addMessage(self::EMERGENCY, (string) $message, $context);
    }

    /**
     * Exclude certain adapters.
     *
     * @param array $adapters
     *
     * @return Logger
     */
    public function excludeAdapters(array $adapters = []): Logger
    {
        /**
         * Loop through what has been passed. Check these names with
         * the registered adapters. If they match, add them to the
         * this->excluded array
         */
        $registered = $this->adapters;

        /**
         * Loop through what has been passed. Check these names with
         * the registered adapters. If they match, add them to the
         * this->excluded array
         */
        foreach ($adapters as $adapter) {
            if (isset($registered[$adapter])) {
                $this->excluded[$adapter] = true;
            }
        }

        return $this;
    }

    /**
     * Returns an adapter from the stack
     *
     * @param string $name The name of the adapter
     *
     * @return AdapterInterface
     * @throws LoggerException
     */
    public function getAdapter(string $name): AdapterInterface
    {
        if (true !== isset($this->adapters[$name])) {
            throw new LoggerException('Adapter does not exist for this logger');
        }

        return $this->adapters[$name];
    }

    /**
     * Returns the adapter stack array
     *
     * @return AdapterInterface[]
     */
    public function getAdapters(): array
    {
        return $this->adapters;
    }

    /**
     * Returns the log level
     */
    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    /**
     * Returns the name of the logger
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function info($message, array $context = []): void
    {
        $this->addMessage(self::INFO, (string) $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function log($level, $message, array $context = []): void
    {
        $intLevel = $this->getLevelNumber($level);

        $this->addMessage($intLevel, (string) $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function notice($message, array $context = []): void
    {
        $this->addMessage(self::NOTICE, (string) $message, $context);
    }

    /**
     * Removes an adapter from the stack
     *
     * @param string $name The name of the adapter
     *
     * @return Logger
     * @throws LoggerException
     */
    public function removeAdapter(string $name): Logger
    {
        if (!isset($this->adapters[$name])) {
            throw new LoggerException('Adapter does not exist for this logger');
        }

        unset($this->adapters[$name]);

        return $this;
    }

    /**
     * Sets the adapters stack overriding what is already there
     *
     * @param array $adapters An array of adapters
     *
     * @return Logger
     */
    public function setAdapters(array $adapters): Logger
    {
        $this->adapters = $adapters;

        return $this;
    }

    /**
     * Sets the adapters stack overriding what is already there
     *
     * @param int $level
     *
     * @return Logger
     */
    public function setLogLevel(int $level): Logger
    {
        $levels         = $this->getLevels();
        $this->logLevel = isset($levels[$level]) ? $level : self::CUSTOM;

        return $this;
    }

    /**
     * LoggerExceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @throws LoggerException
     */
    public function warning($message, array $context = []): void
    {
        $this->addMessage(self::WARNING, (string) $message, $context);
    }

    /**
     * Adds a message to each handler for processing
     *
     * @param int    $level
     * @param string $message
     * @param array  $context
     *
     * @return bool
     * @throws Exception
     * @throws LoggerException
     */
    protected function addMessage(
        int $level,
        string $message,
        array $context = []
    ): bool {
        if ($this->logLevel >= $level) {
            if (count($this->adapters) === 0) {
                throw new LoggerException('No adapters specified');
            }

            $levels    = $this->getLevels();
            $levelName = $levels[$level] ?? self::CUSTOM;

            $item = new Item(
                $message,
                $levelName,
                $level,
                new DateTimeImmutable('now', $this->timezone),
                $context
            );

            /**
             * Log only if the key does not exist in the excluded ones
             */
            foreach ($this->adapters as $name => $adapter) {
                if (!isset($this->excluded[$name])) {
                    if ($adapter->inTransaction()) {
                        $adapter->add($item);
                    } else {
                        $adapter->process($item);
                    }
                }
            }

            /**
             * Clear the excluded array since we made the call now
             */
            $this->excluded = [];
        }

        return true;
    }

    /**
     * Returns an array of log levels with integer to string conversion
     */
    protected function getLevels(): array
    {
        return [
            self::ALERT     => "alert",
            self::CRITICAL  => "critical",
            self::DEBUG     => "debug",
            self::EMERGENCY => "emergency",
            self::ERROR     => "error",
            self::INFO      => "info",
            self::NOTICE    => "notice",
            self::WARNING   => "warning",
            self::CUSTOM    => "custom",
        ];
    }

    /**
     * Converts the level from string/word to an integer
     *
     * @param mixed $level
     *
     * @return int
     */
    private function getLevelNumber($level): int
    {
        if (is_string($level)) {
            $levelName = mb_strtolower($level);
            $levels    = array_flip($this->getLevels());

            if (isset($levels[$levelName])) {
                return $levels[$levelName];
            }
        } elseif (is_numeric($level)) {
            $levels = $this->getLevels();

            if (isset($levels[$level])) {
                return (int) $level;
            }
        }

        return self::CUSTOM;
    }
}
