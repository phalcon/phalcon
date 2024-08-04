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

namespace Phalcon\Tests\Unit\Logger\Logger;

use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\Enum;
use Phalcon\Logger\Formatter\Line;
use Phalcon\Logger\Logger;
use Phalcon\Tests\UnitTestCase;

use function file_get_contents;
use function logsDir;
use function sprintf;
use function strtoupper;
use function uniqid;

final class LogTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Logger :: log()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerLog(): void
    {
        $logPath  = logsDir();
        $fileName = $this->getNewFileName('log', 'log');
        $adapter  = new Stream($logPath . $fileName);

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $levels = [
            Enum::ALERT     => 'alert',
            Enum::CRITICAL  => 'critical',
            Enum::DEBUG     => 'debug',
            Enum::EMERGENCY => 'emergency',
            Enum::ERROR     => 'error',
            Enum::INFO      => 'info',
            Enum::NOTICE    => 'notice',
            Enum::WARNING   => 'warning',
            Enum::CUSTOM    => 'custom',
            'alert'         => 'alert',
            'critical'      => 'critical',
            'debug'         => 'debug',
            'emergency'     => 'emergency',
            'error'         => 'error',
            'info'          => 'info',
            'notice'        => 'notice',
            'warning'       => 'warning',
            'custom'        => 'custom',
            99              => 'custom',
        ];

        foreach ($levels as $level => $levelName) {
            $logger->log($level, 'Message ' . $levelName);
        }

        $contents = file_get_contents($logPath . $fileName);
        foreach ($levels as $levelName) {
            $expected = sprintf(
                '[%s] Message %s',
                strtoupper($levelName),
                $levelName
            );

            $this->assertStringContainsString($expected, $contents);
        }

        $adapter->close();
        $this->safeDeleteFile($fileName);
    }

    /**
     * Tests Phalcon\Logger :: log() - logLevel
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerLogLogLevel(): void
    {
        $logPath  = logsDir();
        $fileName = $this->getNewFileName('log', 'log');
        $adapter  = new Stream($logPath . $fileName);

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $logger->setLogLevel(Enum::ALERT);

        $levelsYes = [
            Enum::ALERT     => 'alert',
            Enum::CRITICAL  => 'critical',
            Enum::EMERGENCY => 'emergency',
            'alert'         => 'alert',
            'critical'      => 'critical',
            'emergency'     => 'emergency',
        ];

        $levelsNo = [
            Enum::DEBUG   => 'debug',
            Enum::ERROR   => 'error',
            Enum::INFO    => 'info',
            Enum::NOTICE  => 'notice',
            Enum::WARNING => 'warning',
            Enum::CUSTOM  => 'custom',
            'debug'       => 'debug',
            'error'       => 'error',
            'info'        => 'info',
            'notice'      => 'notice',
            'warning'     => 'warning',
            'custom'      => 'custom',
        ];

        foreach ($levelsYes as $level => $levelName) {
            $logger->log($level, 'Message ' . $levelName);
        }

        foreach ($levelsNo as $level => $levelName) {
            $logger->log($level, 'Message ' . $levelName);
        }

        $contents = file_get_contents($logPath . $fileName);
        foreach ($levelsYes as $levelName) {
            $expected = sprintf(
                '[%s] Message %s',
                strtoupper($levelName),
                $levelName
            );
            $this->assertStringContainsString($expected, $contents);
        }

        foreach ($levelsNo as $levelName) {
            $expected = sprintf(
                '[%s] Message %s',
                strtoupper($levelName),
                $levelName
            );
            $this->assertStringNotContainsString($expected, $contents);
        }

        $adapter->close();
        $this->safeDeleteFile($fileName);
    }

    /**
     * Tests Phalcon\Logger :: log() - interpolator
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-09-11
     */
    public function testLoggerLogLogInterpolator(): void
    {
        $logPath   = logsDir();
        $fileName  = $this->getNewFileName('log', 'log');
        $formatter = new Line(
            '%message%-[%level%]-%server%:%user%',
            'U.u'
        );
        $context   = [
            'server' => uniqid('srv-'),
            'user'   => uniqid('usr-'),
        ];
        $adapter   = new Stream($logPath . $fileName);
        $adapter->setFormatter($formatter);

        $logger = new Logger(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $logger->log(Enum::DEBUG, 'log message', $context);

        $contents = file_get_contents($logPath . $fileName);
        $expected = sprintf(
            'log message-[DEBUG]-%s:%s',
            $context['server'],
            $context['user']
        );
        $this->assertStringContainsString($expected, $contents);

        $adapter->close();
        $this->safeDeleteFile($fileName);
    }
}
