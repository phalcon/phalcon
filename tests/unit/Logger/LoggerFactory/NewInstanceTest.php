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

namespace Phalcon\Tests\Unit\Logger\LoggerFactory;

use Phalcon\Logger\Adapter\Stream;
use Phalcon\Logger\AdapterFactory;
use Phalcon\Logger\Logger;
use Phalcon\Logger\LoggerFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use Psr\Log\LoggerInterface;

use function logsDir;

/**
 * Class NewInstanceTest extends AbstractUnitTestCase
 *
 * @package Phalcon\Tests\Unit\Logger\LoggerFactory
 */
final class NewInstanceTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Logger\LoggerFactory :: newInstance()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerLoggerFactoryNewInstance(): void
    {
        $logPath = logsDir();
        $fileName = $this->getNewFileName('log', 'log');
        $adapter = new Stream($logPath . $fileName);
        $factory = new LoggerFactory(new AdapterFactory());
        $logger = $factory->newInstance(
            'my-logger',
            [
                'one' => $adapter,
            ]
        );

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }
}
