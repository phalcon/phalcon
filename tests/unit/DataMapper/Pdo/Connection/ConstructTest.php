<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\DataMapper\Pdo\Connection;

use Phalcon\Tests\DatabaseTestCase;
use InvalidArgumentException;
use Phalcon\DataMapper\Pdo\Connection;

final class ConstructTest extends DatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __construct()
     *
     * @since  2020-01-25
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionConstruct(): void
    {
        /** @var Connection $connection */
        $connection = $this->getDataMapperConnection();

        $this->assertInstanceOf(Connection::class, $connection);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __construct() -
     * exception
     *
     * @since  2020-01-20
     *
     * @group  pgsql
     * @group  mysql
     * @group  sqlite
     */
    public function testDmPdoConnectionConstructException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver not supported [random]');

        (new Connection('random:some data'));
    }
}
