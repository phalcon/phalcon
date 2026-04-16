<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use PDO;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;

use function explode;

final class GetAvailableDriversTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: getAvailableDrivers()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionGetAvailableDrivers(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $expected = PDO::getAvailableDrivers();
        $actual   = $connection::getAvailableDrivers();

        $this->assertSame($expected, $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: getDriverName()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionGetDriverName(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $dsn = self::getDatabaseDsn();
        $dsn = explode(':', $dsn);

        $expected = $dsn[0];
        $actual   = $connection->getDriverName();
        $this->assertSame($expected, $actual);
    }
}
