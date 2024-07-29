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

namespace Phalcon\Logger\Adapter;

use LogicException;
use Phalcon\Logger\Enum;
use Phalcon\Logger\Item;

use function closelog;
use function openlog;
use function sprintf;

use const LOG_ALERT;
use const LOG_CRIT;
use const LOG_DEBUG;
use const LOG_EMERG;
use const LOG_ERR;
use const LOG_INFO;
use const LOG_NOTICE;
use const LOG_ODELAY;
use const LOG_USER;
use const LOG_WARNING;

/**
 * Class Syslog
 *
 * @property string $defaultFormatter
 * @property int    $facility
 * @property string $name
 * @property bool   $opened
 * @property int    $option
 */
class Syslog extends AbstractAdapter
{
    /**
     * @var int
     */
    protected int $facility = 0;

    /**
     * @var bool
     */
    protected bool $opened = false;

    /**
     * @var int
     */
    protected int $option = 0;

    /**
     * Syslog constructor.
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct(
        protected string $name,
        array $options = []
    ) {
        $this->option   = $options['option'] ?? LOG_ODELAY;
        $this->facility = $options['facility'] ?? LOG_USER;
    }

    /**
     * Closes the logger
     */
    public function close(): bool
    {
        if (true !== $this->opened) {
            return true;
        }

        return closelog();
    }

    /**
     * Processes the message i.e. writes it to the syslog
     *
     * @param Item $item
     *
     * @throws LogicException
     */
    public function process(Item $item): void
    {
        $message = $this->getFormattedItem($item);
        $result  = $this->openlog($this->name, $this->option, $this->facility);

        if (!$result) {
            throw new LogicException(
                sprintf(
                    "Cannot open syslog for name [%s] and facility [%s]",
                    $this->name,
                    $this->facility
                )
            );
        }

        $this->opened = true;
        $level        = $this->logLevelToSyslog($item->getLevel());

        \syslog($level, $message);
    }

    /**
     * Open connection to system logger
     *
     * @link https://php.net/manual/en/function.openlog.php
     *
     * @param string $ident
     * @param int    $option
     * @param int    $facility
     *
     * @return bool
     */
    protected function openlog(string $ident, int $option, int $facility)
    {
        return openlog($ident, $option, $facility);
    }

    /**
     * Translates a Logger level to a Syslog level
     *
     * @param int $level
     *
     * @return int
     */
    private function logLevelToSyslog(int $level): int
    {
        $levels = [
            Enum::ALERT     => LOG_ALERT,
            Enum::CRITICAL  => LOG_CRIT,
            Enum::CUSTOM    => LOG_ERR,
            Enum::DEBUG     => LOG_DEBUG,
            Enum::EMERGENCY => LOG_EMERG,
            Enum::ERROR     => LOG_ERR,
            Enum::INFO      => LOG_INFO,
            Enum::NOTICE    => LOG_NOTICE,
            Enum::WARNING   => LOG_WARNING,
        ];

        return $levels[$level] ?? LOG_ERR;
    }
}
