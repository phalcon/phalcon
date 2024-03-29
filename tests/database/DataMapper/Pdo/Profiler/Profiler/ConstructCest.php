<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Profiler\Profiler;

use DatabaseTester;
use Phalcon\DataMapper\Pdo\Profiler\Profiler;

class ConstructCest
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Profiler\Profiler :: __construct()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function dMPdoProfilerProfilerConstruct(DatabaseTester $I)
    {
        $I->wantToTest('DataMapper\Pdo\Profiler\Profiler - __construct()');

        $profiler = new Profiler();

        $I->assertInstanceOf(Profiler::class, $profiler);
    }
}
