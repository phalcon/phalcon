<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Profiler\Profiler;

use InvalidArgumentException;
use Phalcon\DataMapper\Pdo\Profiler\MemoryLogger;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\DataMapper\Pdo\ProfilerJsonEncodeFixture;

use function sleep;

final class StartFinishTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * start()/finish()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoProfilerProfilerStartFinish(): void
    {
        $profiler = new Profiler(new MemoryLogger());

        $profiler
            ->setActive(true)
            ->start('my-method')
        ;

        sleep(1);
        $profiler->finish('select from something', [1 => 2]);

        $logger = $profiler->getLogger();
        $actual = $logger->getMessages()[0];

        $expected = 'M: my-method (';
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: select from something';
        $this->assertStringContainsString($expected, $actual);

        $expected = 'V: {"1":2}';
        $this->assertStringContainsString($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * start()/finish()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoProfilerProfilerStartFinishEmptyValues(): void
    {
        $profiler = new Profiler(new MemoryLogger());

        $profiler
            ->setActive(true)
            ->start('my-method')
        ;

        sleep(1);
        $profiler->finish('select from something');

        $logger = $profiler->getLogger();
        $actual = $logger->getMessages()[0];

        $expected = 'M: my-method (';
        $this->assertStringContainsString($expected, $actual);

        $expected = 'S: select from something';
        $this->assertStringContainsString($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * start()/finish()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoProfilerProfilerStartFinishEncodeException(): void
    {
        /**
         * Although this returns `No error`, we are mocking `json_encode` to
         * see if the exception code gets executed
         */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('json_encode error: No error');
        $profiler = new ProfilerJsonEncodeFixture();

        $profiler
            ->setActive(true)
            ->start('my-method')
        ;
        $profiler->finish('select from something', [1 => 2]);
    }
}
