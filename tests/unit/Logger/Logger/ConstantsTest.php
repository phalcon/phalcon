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

namespace Phalcon\Tests\Unit\Logger\Logger;

use Phalcon\Logger\Enum;
use Phalcon\Logger\Logger;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConstantsTest extends AbstractUnitTestCase
{
    /**
     * @return array<string, array{0: int, 1: int}>
     */
    public static function getExamples(): array
    {
        return [
            'ALERT'     => [Logger::ALERT, Enum::ALERT],
            'CRITICAL'  => [Logger::CRITICAL, Enum::CRITICAL],
            'CUSTOM'    => [Logger::CUSTOM, Enum::CUSTOM],
            'DEBUG'     => [Logger::DEBUG, Enum::DEBUG],
            'EMERGENCY' => [Logger::EMERGENCY, Enum::EMERGENCY],
            'ERROR'     => [Logger::ERROR, Enum::ERROR],
            'INFO'      => [Logger::INFO, Enum::INFO],
            'NOTICE'    => [Logger::NOTICE, Enum::NOTICE],
            'TRACE'     => [Logger::TRACE, Enum::TRACE],
            'WARNING'   => [Logger::WARNING, Enum::WARNING],
        ];
    }

    /**
     * The logger has the level constants of cphalcon, with the values of
     * Phalcon\Logger\Enum.
     *
     * @issue  https://github.com/phalcon/phalcon/issues/833
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    #[DataProvider('getExamples')]
    public function testLoggerLoggerConstants(int $actual, int $expected): void
    {
        $this->assertSame($expected, $actual);
    }
}
