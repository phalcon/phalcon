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

namespace Phalcon\Tests\Database\Db\Profiler;

use Phalcon\Db\Profiler\Item;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Migrations\InvoicesMigration;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

use function substr;
use function uniqid;

final class ProfilerTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Db\Profiler :: full
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-11-30
     *
     * @group mysql
     */
    public function testDbProfilerFull(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $eventsManager = $this->newService('eventsManager');
        $profiler      = $this->newService('profiler');
        $connection    = $this->getService('db');

        $eventsManager->attach(
            'db',
            function ($event, $connection) use ($profiler) {
                if ($event->getType() === 'beforeQuery') {
                    $profiler->startProfile(
                        $connection->getSQLStatement()
                    );
                }

                if ($event->getType() === 'afterQuery') {
                    $profiler->stopProfile();
                }
            }
        );

        $connection->setEventsManager($eventsManager);

        $migration = new InvoicesMigration(self::getConnection());
        $title     = uniqid('tit-');
        $migration->insert(10, 20, 1, $title, 100);

        $invoices = Invoices::find();

        $expected = 1;
        $actual   = $invoices->count();
        $this->assertSame($expected, $actual);

        $profiles = $profiler->getProfiles();
        $this->assertCount(3, $profiles);

        /**
         * First
         */
        /** @var Item $first */
        $first = $profiles[0];

        $nanoseconds = $first->getTotalElapsedNanoseconds();
        $miliseconds = $nanoseconds / 1000000;
        $seconds     = $miliseconds / 1000;

        $miliseconds = substr((string)$miliseconds, 0, 5);
        $seconds     = substr((string)$seconds, 0, 5);

        $expected = $miliseconds;
        $actual   = substr((string)$first->getTotalElapsedMilliseconds(), 0, 5);
        $this->assertSame($expected, $actual);

        $expected = $seconds;
        $actual   = substr((string)$first->getTotalElapsedSeconds(), 0, 5);
        $this->assertSame($expected, $actual);

        /**
         * Active
         */
        $active = $profiler->getLastProfile();
        $last = $profiles[2];
        $this->assertSame($last, $active);

        /**
         * Profile
         */
        $elapsed = $profiles[0]->getTotalElapsedSeconds()
            + $profiles[1]->getTotalElapsedSeconds()
            + $profiles[2]->getTotalElapsedSeconds();

        $elapsed = substr((string)$elapsed, 0, 5);

        $expected = $elapsed;
        $actual   = substr((string)$profiler->getTotalElapsedSeconds(), 0, 5);
        $this->assertSame($expected, $actual);

        $expected = 3;
        $actual   = $profiler->getNumberTotalStatements();
        $this->assertSame($expected, $actual);

        /**
         * Reset
         */
        $profiler->reset();

        $profiles = $profiler->getProfiles();
        $this->assertCount(0, $profiles);
    }
}
