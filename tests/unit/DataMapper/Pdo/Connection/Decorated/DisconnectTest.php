<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection\Decorated;

use Phalcon\Tests\DatabaseTestCase;
use PDO;
use Phalcon\DataMapper\Pdo\Connection\Decorated;
use Phalcon\DataMapper\Pdo\Exception\CannotDisconnect;

final class DisconnectTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection\Decorated ::
     * disconnect()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionDecoratedDisconnect(): void
    {
        $this->expectException(CannotDisconnect::class);
        $this->expectExceptionMessage(
            "Cannot disconnect a Decorated connection instance"
        );

        $connection = new PDO(
            $this->getDatabaseDsn(),
            $this->getDatabaseUsername(),
            $this->getDatabasePassword()
        );

        $decorated = new Decorated($connection);
        $decorated->disconnect();
    }
}
