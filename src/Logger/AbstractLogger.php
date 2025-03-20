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
use Psr\Log\LogLevel;

use function array_diff_key;
use function array_flip;
use function date_default_timezone_get;
use function is_numeric;
use function is_string;
use function strtolower;

/**
 * Abstract Logger Class
 *
 * Abstract logger class, providing common functionality. A formatter interface
 * is available as well as an adapter one. Adapters can be created easily using
 * the built in AdapterFactory. A LoggerFactory is also available that allows
 * developers to create new instances of the Logger or load them from config
 * files (see Phalcon\Config\Config object).
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
    protected int $logLevel = Enum::CUSTOM;

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
     *
     * @throws Exception
     */
    public function __construct(
        protected string $name,
        array $adapters = [],
        DateTimeZone | null $timezone = null
    ) {
        if (null == $timezone) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

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
         * $this->excluded array
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
        if (!isset($this->adapters[$name])) {
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
        $this->logLevel = isset($levels[$level]) ? $level : Enum::CUSTOM;

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
            $levelName = $levels[$level] ?? $levels[Enum::CUSTOM];

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
    protected function getLevelNumber(mixed $level): int
    {
        if (is_string($level)) {
            $levelName = strtolower($level);
            $levels    = array_flip($this->getLevels());

            return $levels[$levelName] ?? Enum::CUSTOM;
        }

        if (is_numeric($level) && isset($this->getLevels()[$level])) {
            return (int)$level;
        }

        return Enum::CUSTOM;
    }

    /**
     * Returns an array of log levels with integer to string conversion
     *
     * @return string[]
     */
    protected function getLevels(): array
    {
        return [
            Enum::ALERT     => LogLevel::ALERT,
            Enum::CRITICAL  => LogLevel::CRITICAL,
            Enum::DEBUG     => LogLevel::DEBUG,
            Enum::EMERGENCY => LogLevel::EMERGENCY,
            Enum::ERROR     => LogLevel::ERROR,
            Enum::INFO      => LogLevel::INFO,
            Enum::NOTICE    => LogLevel::NOTICE,
            Enum::WARNING   => LogLevel::WARNING,
            Enum::CUSTOM    => 'custom',
        ];
    }
}
