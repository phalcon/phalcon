<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection;

use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\DatabaseTestCase;

final class ConnectDisconnectIsConnectedTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection ::
     * connect()/disconnect()/isConnected()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionConnectDisconnectIsConnected(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $this->assertFalse($connection->isConnected());
        $connection->connect();
        $this->assertTrue($connection->isConnected());
        $connection->disconnect();
        $this->assertFalse($connection->isConnected());
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: connect() - queries
     *
     * @since  2020-01-25
     *
     * @group  common
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
            $result = $connection->fetchOne(
                'show variables like "character_set_client"'
            );

            $this->assertTrue($connection->isConnected());
            $expected = [
                'Variable_name' => 'character_set_client',
                'Value'         => 'big5',
            ];

            $this->assertEquals($expected, $result);
        }
    }
}
