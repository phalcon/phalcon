<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection;

use BadMethodCallException;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\Fixtures\DataMapper\Pdo\ConnectionFixture;

final class UnderscoreCallTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __call()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionUnderscoreCall(): void
    {
        /** @var Connection $connection */
        $connection = new ConnectionFixture(
            $this->getDatabaseDsn(),
            $this->getDatabaseUsername(),
            $this->getDatabasePassword()
        );

        $actual = $connection->callMe('blondie');
        $this->assertEquals('blondie', $actual);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __call() - exception
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionUnderscoreCallException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            "Class 'Phalcon\DataMapper\Pdo\Connection' does not have a method 'unknown'"
        );

        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $connection->unknown();
    }
}
