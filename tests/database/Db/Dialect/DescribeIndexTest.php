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

namespace Phalcon\Tests\Database\Db\Dialect;

use Phalcon\Db\Dialect\Mysql;
use Phalcon\Db\Dialect\Postgresql;
use Phalcon\Db\Dialect\Sqlite;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class DescribeIndexTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: describeIndex
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group sqlite
     */
    public function testDbDialectDescribeIndexes(): void
    {
        $dialect = new Sqlite();

        $expected = "PRAGMA index_info('table')";
        $actual   = $dialect->describeIndex('table');
        $this->assertSame($expected, $actual);
    }
}
