<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use DatabaseTester;
use Phalcon\DataMapper\Pdo\Connection;

class ConnectDisconnectIsConnectedCest
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection ::
     * connect()/disconnect()/isConnected()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function dMPdoConnectionConnectDisconnectIsConnected(DatabaseTester $I)
    {
        $I->wantToTest('DataMapper\Pdo\Connection - connect()/disconnect()/isConnected()');

        /** @var Connection $connection */
        $connection = $I->getDataMapperConnection();

        $I->assertFalse($connection->isConnected());
        $connection->connect();
        $I->assertTrue($connection->isConnected());
        $connection->disconnect();
        $I->assertFalse($connection->isConnected());
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: connect() - queries
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function dMPdoConnectionConnectQueries(DatabaseTester $I)
    {
        $I->wantToTest('DataMapper\Pdo\Connection - connect() - queries');

        if ('mysql' === $I->getDriver()) {
            /** @var Connection $connection */
            $connection = new Connection(
                $I->getDatabaseDsn(),
                $I->getDatabaseUsername(),
                $I->getDatabasePassword(),
                [],
                [
                    'set names big5',
                ]
            );

            $I->assertFalse($connection->isConnected());
            $result = $connection->fetchOne(
                'show variables like "character_set_client"'
            );

            $I->assertTrue($connection->isConnected());
            $expected = [
                'Variable_name' => 'character_set_client',
                'Value'         => 'big5',
            ];

            $I->assertEquals($expected, $result);
        }
    }
}
