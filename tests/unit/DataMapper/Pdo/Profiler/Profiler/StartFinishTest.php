<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Profiler\Profiler;

use InvalidArgumentException;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Tests\Fixtures\DataMapper\Pdo\ProfilerJsonEncodeFixture;

use function sleep;
use function strpos;

final class StartFinishTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * start()/finish()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoProfilerProfilerStartFinish(): void
    {
        $profiler = new Profiler();

        $profiler
            ->setActive(true)
            ->start('my-method')
        ;

        sleep(1);
        $profiler->finish('select from something', [1 => 2]);

        $logger  = $profiler->getLogger();
        $message = $logger->getMessages()[0];

        $this->assertNotFalse(strpos($message, 'my-method ('));
        $this->assertNotFalse(strpos($message, 'select from something #0'));
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * start()/finish()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoProfilerProfilerStartFinishEmptyValues(): void
    {
        $profiler = new Profiler();

        $profiler
            ->setActive(true)
            ->start('my-method')
        ;

        sleep(1);
        $profiler->finish('select from something');

        $logger  = $profiler->getLogger();
        $message = $logger->getMessages()[0];

        $this->assertNotFalse(strpos($message, 'my-method ('));
        $this->assertNotFalse(strpos($message, 'select from something #0'));
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler ::
     * start()/finish()
     *
     * @since  2020-01-25
     *
     * @group  common
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
