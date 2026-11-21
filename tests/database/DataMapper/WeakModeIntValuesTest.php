<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\DataMapper;

use PDOException;
use Phalcon\DataMapper\Pdo\Connection;
use Phalcon\DataMapper\Pdo\ConnectionLocator;
use Phalcon\DataMapper\Query\Bind;
use Phalcon\DataMapper\Query\QueryFactory;
use Phalcon\Tests\AbstractDatabaseTestCase;
use PHPUnit\Framework\Attributes\Group;

use function sprintf;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
#[Group('mysql')]
#[Group('pgsql')]
#[Group('sqlite')]
final class WeakModeIntValuesTest extends AbstractDatabaseTestCase
{
    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperPdoConnectionLocatorListOfFactories(): void
    {
        $connection = self::getDataMapperConnection();
        $factory    = function () use ($connection) {
            return $connection;
        };

        $locator = new ConnectionLocator($connection, [$factory], [$factory]);

        $this->assertSame($connection, $locator->getRead('0'));
        $this->assertSame($connection, $locator->getWrite('0'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperPdoConnectionQueryInt(): void
    {
        $connection = new Connection(
            self::getDatabaseDsn(),
            self::getDatabaseUsername(),
            self::getDatabasePassword(),
            [],
            [123]
        );

        $this->expectException(PDOException::class);

        $connection->connect();
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperQueryBindSetValuesIntKeys(): void
    {
        $bind = new Bind();
        $bind->setValues(['x']);

        $this->assertSame([0 => ['x', 2]], $bind->toArray());

        $select = (new QueryFactory())->newSelect(self::getDataMapperConnection());
        $select->bindValues(['2024' => 'x']);

        $this->assertSame([2024 => ['x', 2]], $select->getBindValues());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperQueryInsertColumnNumericName(): void
    {
        $connection = self::getDataMapperConnection();
        $quotes     = $connection->getQuoteNames();
        $insert     = (new QueryFactory())->newInsert($connection);
        $insert->into('t')->column('2024', 5);

        $this->assertSame(
            sprintf(
                'INSERT INTO t (%s2024%s) VALUES (:2024)',
                $quotes['prefix'],
                $quotes['suffix']
            ),
            $insert->getStatement()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperQueryInsertColumnsListInt(): void
    {
        $connection = self::getDataMapperConnection();
        $quotes     = $connection->getQuoteNames();
        $insert     = (new QueryFactory())->newInsert($connection);
        $insert->into('t')->columns([5]);

        $this->assertSame(
            sprintf(
                'INSERT INTO t (%s5%s) VALUES (:5)',
                $quotes['prefix'],
                $quotes['suffix']
            ),
            $insert->getStatement()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperQuerySelectWhereEqualsListInt(): void
    {
        $select = (new QueryFactory())->newSelect(self::getDataMapperConnection());
        $select->from('t')->whereEquals([1]);

        $this->assertSame('SELECT * FROM t WHERE 1', $select->getStatement());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperQueryUpdateColumnNumericName(): void
    {
        $connection = self::getDataMapperConnection();
        $quotes     = $connection->getQuoteNames();
        $update     = (new QueryFactory())->newUpdate($connection);
        $update->from('t')->column('2024', 5);

        $this->assertSame(
            sprintf(
                'UPDATE t SET %s2024%s = :2024',
                $quotes['prefix'],
                $quotes['suffix']
            ),
            $update->getStatement()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testDataMapperQueryUpdateColumnsListInt(): void
    {
        $connection = self::getDataMapperConnection();
        $quotes     = $connection->getQuoteNames();
        $update     = (new QueryFactory())->newUpdate($connection);
        $update->from('t')->columns([5]);

        $this->assertSame(
            sprintf(
                'UPDATE t SET %s5%s = :5',
                $quotes['prefix'],
                $quotes['suffix']
            ),
            $update->getStatement()
        );
    }
}
