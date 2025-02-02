<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetSetProfilerTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: getProfiler()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionGetProfiler(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $actual = $connection->getProfiler();
        $this->assertNull($actual);

        $profiler = new Profiler();
        $connection->setProfiler($profiler);

        $expected = $profiler;
        $actual   = $connection->getProfiler();
        $this->assertSame($expected, $actual);
    }
}
