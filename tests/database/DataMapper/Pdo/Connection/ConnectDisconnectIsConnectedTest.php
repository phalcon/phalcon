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
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ConnectDisconnectIsConnectedTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection ::
     * connect()/disconnect()/isConnected()
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionConnectDisconnectIsConnected(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $this->assertFalse($connection->isConnected());
        $connection->connect();
        $this->assertTrue($connection->isConnected());
        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
        $connection->connect();
        $this->assertTrue($connection->isConnected());
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: connect() - queries
     *
     * @since  2020-01-25
     *
     * @group mysql
     */
    public function testDmPdoConnectionConnectQueries(): void
    {
        if ('mysql' === self::getDriver()) {
            /** @var Connection $connection */
            $connection = new Connection(
                $this->getDatabaseDsn(),
                $this->getDatabaseUsername(),
                $this->getDatabasePassword(),
                [],
                [
                    'set names big5',
                ]
            );

            $this->assertFalse($connection->isConnected());

            $expected = [
                'Variable_name' => 'character_set_client',
                'Value'         => 'big5',
            ];
            $actual   = $connection->fetchOne(
                'show variables like "character_set_client"'
            );
            $this->assertSame($expected, $actual);
        }
    }
}
