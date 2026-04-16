<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Profiler\Profiler;

use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetSetLogFormatTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * getLogFormat()/setLogFormat()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoProfilerProfilerGetSetLogFormat(): void
    {
        $profiler = new Profiler();

        $expected = "M: {method} ({duration}s)"
            . PHP_EOL
            . "S: {statement}"
            . PHP_EOL
            . "V: {values}"
            . PHP_EOL
            . "B: {backtrace}";
        $actual   = $profiler->getLogFormat();
        $this->assertSame($expected, $actual);

        $format = "{method} ({duration}s): {statement}";
        $profiler->setLogFormat($format);

        $expected = $format;
        $actual   = $profiler->getLogFormat();
        $this->assertSame($expected, $actual);
    }
}
