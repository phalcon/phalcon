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

use function array_diff_key;
use function array_flip;
use function date_default_timezone_get;
use function is_numeric;
use function is_string;
use function strtoupper;

/**
 * Abstract Logger Class
 *
 * A PSR compatible logger, with various adapters and formatters. A formatter
 * interface is available as well as an adapter one. Adapters can be created
 * easily using the built in AdapterFactory. A LoggerFactory is also available
 * that allows developers to create new instances of the Logger or load them
 * from config files (see Phalcon\Config\Config object).
 *
 * @package Phalcon\Logger
 *
 * @property AdapterInterface[] $adapters
 * @property array              $excluded
 * @property int                $logLevel
 * @property string             $name
 * @property DateTimeZone       $timezone
 */
abstract class AbstractLogger
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
     * @param string            $name     The name of the logger
     * @param array             $adapters The collection of adapters to be used
     *                                    for logging (default [])
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
     * @return AbstractLogger
     */
    public function addAdapter(string $name, AdapterInterface $adapter): AbstractLogger
    {
        $this->adapters[$name] = $adapter;

        return $this;
    }

    /**
     * Exclude certain adapters.
     *
     * @param array $adapters
     *
     * @return AbstractLogger
     */
    public function excludeAdapters(array $adapters = []): AbstractLogger
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
            throw new LoggerException(
                'Adapter does not exist for this logger'
            );
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
     * Removes an adapter from the stack
     *
     * @param string $name The name of the adapter
     *
     * @return AbstractLogger
     * @throws LoggerException
     */
    public function removeAdapter(string $name): AbstractLogger
    {
        if (!isset($this->adapters[$name])) {
            throw new LoggerException(
                'Adapter does not exist for this logger'
            );
        }

        unset($this->adapters[$name]);

        return $this;
    }

    /**
     * Sets the adapters stack overriding what is already there
     *
     * @param array $adapters An array of adapters
     *
     * @return AbstractLogger
     */
    public function setAdapters(array $adapters): AbstractLogger
    {
        $this->adapters = $adapters;

        return $this;
    }

    /**
     * Sets the adapters stack overriding what is already there
     *
     * @param int $level
     *
     * @return AbstractLogger
     */
    public function setLogLevel(int $level): AbstractLogger
    {
        $levels         = $this->getLevels();
        $this->logLevel = isset($levels[$level]) ? $level : self::CUSTOM;

        return $this;
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
            $levelName = $levels[$level] ?? $levels[self::CUSTOM];

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
            $collection = array_diff_key($this->adapters, $this->excluded);
            foreach ($collection as $adapter) {
                $method = 'process';
                if (true === $adapter->inTransaction()) {
                    $method = 'add';
                }

                $adapter->$method($item);
            }

            /**
             * Clear the excluded array since we made the call now
             */
            $this->excluded = [];
        }

        return true;
    }

    /**
     * Converts the level from string/word to an integer
     *
     * @param mixed $level
     *
     * @return int
     */
    protected function getLevelNumber($level): int
    {
        /**
         * If someone uses "critical" as the level (string)
         */
        if (true === is_string($level)) {
            $levelName = strtoupper($level);
            $levels    = array_flip($this->getLevels());

            if (isset($levels[$levelName])) {
                return $levels[$levelName];
            }
        } elseif (true === is_numeric($level)) {
            $levels = $this->getLevels();

            if (isset($levels[$level])) {
                return (int) $level;
            }
        }

        return self::CUSTOM;
    }

    /**
     * Returns an array of log levels with integer to string conversion
     *
     * @return string[]
     */
    protected function getLevels(): array
    {
        return [
            self::ALERT     => "ALERT",
            self::CRITICAL  => "CRITICAL",
            self::DEBUG     => "DEBUG",
            self::EMERGENCY => "EMERGENCY",
            self::ERROR     => "ERROR",
            self::INFO      => "INFO",
            self::NOTICE    => "NOTICE",
            self::WARNING   => "WARNING",
            self::CUSTOM    => "CUSTOM",
        ];
    }
}
