<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper\Pdo\Connection;

use Closure;
use InvalidArgumentException;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\Tests\AbstractDatabaseTestCase;

final class ConstructTest extends AbstractDatabaseTestCase
{
    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __construct()
     *
     * @since  2020-01-25
     *
     * @group  common
     */
    public function testDmPdoConnectionConstruct(): void
    {
        /** @var Connection $connection */
        $connection = self::getDataMapperConnection();

        $this->assertInstanceOf(Connection::class, $connection);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __construct() -
     * exception
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmPdoConnectionConstructException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver not supported [random]');

        (new Connection('random:some data'));
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: __construct() -
     * exception
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmPdoConnectionNewException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DSN cannot be empty');

        Connection::new('');
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: new
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmPdoConnectionNew(): void
    {
        $connection = Connection::new(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );

        $this->assertInstanceOf(Connection::class, $connection);
    }

    /**
     * Database Tests Phalcon\DataMapper\Pdo\Connection :: factory
     *
     * @since  2020-01-20
     *
     * @group  common
     */
    public function testDmPdoConnectionFactory(): void
    {
        $factory = Connection::factory(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword()
        );

        $this->assertInstanceOf(Closure::class, $factory);

        $connection = $factory();
        $this->assertInstanceOf(Connection::class, $connection);
    }
}
