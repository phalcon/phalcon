<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Logger\Adapter\Syslog;

use Phalcon\Logger\Adapter\Syslog;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TypeError;

use const LOG_DAEMON;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                [],
                'option',
                LOG_ODELAY,
            ],
            [
                ['option' => LOG_ALERT | LOG_INFO],
                'option',
                LOG_ALERT | LOG_INFO,
            ],
            [
                [],
                'facility',
                LOG_USER,
            ],
            [
                ['facility' => LOG_DAEMON],
                'facility',
                LOG_DAEMON,
            ],
        ];
    }

    /**
     * @return array<int, array{0: string}>
     */
    public static function getOptionNames(): array
    {
        return [
            ['option'],
            ['facility'],
        ];
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/867
     */
    #[DataProvider('getOptionNames')]
    public function testLoggerAdapterSyslogConstructNotNumeric(
        string $option
    ): void {
        $this->expectException(TypeError::class);

        new Syslog($this->getNewFileName('log', 'log'), [$option => 'abc']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/867
     */
    #[DataProvider('getOptionNames')]
    public function testLoggerAdapterSyslogConstructNumericString(
        string $option
    ): void {
        $adapter = new Syslog(
            $this->getNewFileName('log', 'log'),
            [$option => (string) LOG_DAEMON]
        );

        $this->assertSame(LOG_DAEMON, $this->getProtectedProperty($adapter, $option));
    }

    /**
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    #[DataProvider('getExamples')]
    public function testLoggerAdapterSyslogConstructOptionsCast(
        array $options,
        string $property,
        int $expected
    ): void {
        $streamName = $this->getNewFileName('log', 'log');

        $adapter  = new Syslog($streamName, $options);
        $property = $this->getProtectedProperty($adapter, $property);

        $this->assertSame($expected, $property);
    }
}
