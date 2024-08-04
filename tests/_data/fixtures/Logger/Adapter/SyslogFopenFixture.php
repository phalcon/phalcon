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

namespace Phalcon\Tests\Fixtures\Logger\Adapter;

use LogicException;
use Phalcon\Logger\Adapter\AbstractAdapter;
use Phalcon\Logger\Adapter\Syslog;
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

class SyslogFopenFixture extends Syslog
{
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
        return false;
    }
}
