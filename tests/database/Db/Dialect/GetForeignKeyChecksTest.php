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
use Phalcon\Tests\AbstractDatabaseTestCase;

final class GetForeignKeyChecksTest extends AbstractDatabaseTestCase
{
    /**
     * Tests Phalcon\Db\Dialect :: getForeignKeyChecks
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-01-20
     *
     * @group mysql
     */
    public function testDbDialectGetForeignKeyChecks(): void
    {
        /** @var Mysql $dialect */
        $dialect = new Mysql();

        $expected = 'SELECT @@foreign_key_checks';
        $actual   = $dialect->getForeignKeyChecks();
        $this->assertSame($expected, $actual);
    }
}
