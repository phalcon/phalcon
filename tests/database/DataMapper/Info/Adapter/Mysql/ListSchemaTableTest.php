<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Info\Adapter\Mysql;

use Phalcon\DataMapper\Info\Adapter\Mysql;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ListSchemaTableTest extends AbstractDatabaseTestCase
{
    /**
     * @since 2025-01-14
     *
     * @group mysql
     */
    public function testDmInfoAdapterMysqlListSchemaName(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $mysql = new Mysql($connection);

        $expected = ['phalcon', 'co_dialect'];
        $actual   = $mysql->listSchemaTable('co_dialect');
        $this->assertSame($expected, $actual);

        $expected = ['phalcon', 'co_dialect'];
        $actual   = $mysql->listSchemaTable('phalcon.co_dialect');
        $this->assertSame($expected, $actual);
    }
}
