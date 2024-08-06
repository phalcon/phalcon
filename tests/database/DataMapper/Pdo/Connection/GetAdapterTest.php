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
use Phalcon\Tests\DatabaseTestCase;

final class GetAdapterTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: getAdapter()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionGetAdapter(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $this->assertFalse($connection->isConnected());

        $connection->connect();

        $this->assertTrue($connection->isConnected());
        $this->assertNotEmpty($connection->getAdapter());

        $connection->disconnect();

        $this->assertNotEmpty(
            $connection->getAdapter(),
            'getPdo() will re-connect if disconnected'
        );
        $this->assertTrue($connection->isConnected());
    }
}
