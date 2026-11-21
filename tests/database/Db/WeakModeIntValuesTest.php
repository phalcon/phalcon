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

namespace Phalcon\Tests\Database\Db;

use Exception as BaseException;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\Adapter\PdoFactory;
use Phalcon\Db\Column;
use Phalcon\Db\Dialect\Mysql;
use Phalcon\Db\Dialect\Postgresql;
use Phalcon\Db\Dialect\Sqlite as SqliteDialect;
use Phalcon\Db\Exception;
use Phalcon\Db\Exceptions\InvalidSqlExpressionType;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Fake\FakePdo;
use Phalcon\Tests\Support\Traits\DiTrait;
use PHPUnit\Framework\Attributes\Group;
use ReflectionObject;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string. The getters of cphalcon return the
 * int with no change, thus the tests use assertEquals for them.
 *
 * @issue https://github.com/phalcon/phalcon/issues/884
 */
final class WeakModeIntValuesTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbAdapterDescribeIndexesIntName(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $db = $this->container->get('db');
        $db->execute('DROP TABLE IF EXISTS ph_weak_int_index');
        $db->execute('CREATE TABLE ph_weak_int_index (id INTEGER PRIMARY KEY, name VARCHAR(10))');

        try {
            $db->execute(
                'CREATE INDEX ' . $db->escapeIdentifier('2024') . ' ON ph_weak_int_index (name)'
            );
            $indexes = $db->describeIndexes('ph_weak_int_index');
        } finally {
            $db->execute('DROP TABLE ph_weak_int_index');
        }

        $this->assertSame('2024', $indexes[2024]->getName());
        $this->assertSame(['name'], $indexes[2024]->getColumns());
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    public function testDbAdapterDescribeReferencesIntName(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();

        $db = $this->container->get('db');
        $db->execute('DROP TABLE IF EXISTS ph_weak_int_child');
        $db->execute('DROP TABLE IF EXISTS ph_weak_int_parent');
        $db->execute('CREATE TABLE ph_weak_int_parent (id INTEGER PRIMARY KEY)');

        try {
            $db->execute(
                'CREATE TABLE ph_weak_int_child (id INTEGER PRIMARY KEY, parent_id INTEGER, '
                . 'CONSTRAINT ' . $db->escapeIdentifier('2024') . ' '
                . 'FOREIGN KEY (parent_id) REFERENCES ph_weak_int_parent (id))'
            );
            $references = $db->describeReferences('ph_weak_int_child');
        } finally {
            $db->execute('DROP TABLE IF EXISTS ph_weak_int_child');
            $db->execute('DROP TABLE ph_weak_int_parent');
        }

        $this->assertSame('2024', $references[2024]->getName());
        $this->assertSame(['parent_id'], $references[2024]->getColumns());
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/900
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    #[Group('sqlite')]
    public function testDbAdapterPdoEscapeStringQuoteFalse(): void
    {
        $pdo              = new FakePdo();
        $pdo->quoteResult = false;

        $db = new Sqlite(['dbname' => ':memory:']);
        (new ReflectionObject($db))->getProperty('pdo')->setValue($db, $pdo);

        /**
         * cphalcon returns false, phalcon returns an empty string
         */
        $this->assertEmpty($db->escapeString('x'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbColumnAfterAndCommentInt(): void
    {
        $column = new Column(
            'a',
            [
                'type'    => Column::TYPE_INTEGER,
                'after'   => 2024,
                'comment' => 2024,
            ]
        );

        $this->assertEquals(2024, $column->getAfterPosition());
        $this->assertEquals(2024, $column->getComment());

        $expected = 'ALTER TABLE `t` ADD `a` INT(0) NOT NULL AFTER `2024`';
        $actual   = (new Mysql())->addColumn('t', '', $column);
        $this->assertSame($expected, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbColumnTypeValuesIntItems(): void
    {
        $column = new Column(
            's',
            [
                'type'       => 'ENUM',
                'typeValues' => [1, 2],
            ]
        );

        $expected = "ENUM('1', '2')";
        $this->assertSame($expected, (new Mysql())->getColumnDefinition($column));
        $this->assertSame($expected, (new Postgresql())->getColumnDefinition($column));
        $this->assertSame($expected, (new SqliteDialect())->getColumnDefinition($column));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbDialectCustomFunctionReturnsInt(): void
    {
        $dialect = new SqliteDialect();
        $dialect->registerCustomFunction(
            'FOO',
            function () {
                return 5;
            }
        );

        $actual = $dialect->getSqlExpression(['type' => 'functionCall', 'name' => 'FOO']);
        $this->assertEquals(5, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbDialectExpressionTypeInt(): void
    {
        $this->expectException(InvalidSqlExpressionType::class);
        $this->expectExceptionMessage("Invalid SQL expression type '5'");

        (new SqliteDialect())->getSqlExpression(['type' => 5]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbDialectIntExpressions(): void
    {
        $dialect = new SqliteDialect();

        $this->assertSame('"2024"', $dialect->getSqlColumn([2024]));
        $this->assertSame('"5"."id"', $dialect->getSqlColumn(['id', 5]));
        $this->assertSame('"id" AS "5"', $dialect->getSqlColumn(['id', '', 5]));
        $this->assertSame(
            '"id" AS "5"',
            $dialect->getSqlColumn(['type' => 'qualified', 'name' => 'id', 'alias' => 5])
        );
        $this->assertSame('"2024"', $dialect->getColumnList([2024]));

        $this->assertSame(
            "'5'",
            $dialect->getSqlExpression(['type' => 'literal', 'value' => 5, 'escape' => true])
        );
        $this->assertEquals(5, $dialect->getSqlExpression(['type' => 'literal', 'value' => 5]));
        $this->assertEquals(1, $dialect->getSqlExpression(['type' => 'placeholder', 'value' => 1]));
        $this->assertSame('"5".*', $dialect->getSqlExpression(['type' => 'all', 'domain' => 5]));
        $this->assertSame(
            '"5"."id"',
            $dialect->getSqlExpression(['type' => 'qualified', 'name' => 'id', 'domain' => 5])
        );
        $this->assertSame(
            '"2024"',
            $dialect->getSqlExpression(['type' => 'qualified', 'name' => 2024])
        );
        $this->assertSame('"2024"', $dialect->getSqlExpression(['type' => 'scalar', 'column' => 2024]));
        $this->assertEquals(5, $dialect->getSqlExpression(['type' => 'scalar', 'value' => 5]));

        $this->assertSame('"2024"', $dialect->getSqlTable([2024]));
        $this->assertSame('"5"."t"', $dialect->getSqlTable(['t', 5]));
        $this->assertSame('"t" AS "5"', $dialect->getSqlTable(['t', null, 5]));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbDialectSelectIntClauses(): void
    {
        $dialect = new SqliteDialect();

        $this->assertSame(
            'SELECT "id" FROM 2024',
            $dialect->select(['columns' => ['id'], 'tables' => 2024])
        );
        $this->assertSame(
            'SELECT "id" FROM "2024"',
            $dialect->select(['columns' => ['id'], 'tables' => [2024]])
        );
        $this->assertSame(
            'SELECT "id" FROM "t"  JOIN "2024" ON 1',
            $dialect->select(['columns' => ['id'], 'tables' => ['t'], 'joins' => [['source' => 2024]]])
        );
        $this->assertSame(
            'SELECT "id" FROM "t" WHERE 1',
            $dialect->select(['columns' => ['id'], 'tables' => ['t'], 'where' => 1])
        );
        $this->assertSame(
            'SELECT "id" FROM "t" GROUP BY 1',
            $dialect->select(['columns' => ['id'], 'tables' => ['t'], 'group' => 1])
        );
        $this->assertSame(
            'SELECT "id" FROM "t" ORDER BY 1',
            $dialect->select(['columns' => ['id'], 'tables' => ['t'], 'order' => 1])
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbDialectSelectJoinsNotIterable(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('The argument is not initialized or iterable()');

        (new SqliteDialect())->select(['columns' => ['id'], 'tables' => ['t'], 'joins' => 1]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbIndexIntColumnName(): void
    {
        $dialect = new Mysql();

        $this->assertSame(
            'ALTER TABLE `t` ADD INDEX `i` (`2024`)',
            $dialect->addIndex('t', '', new Index('i', [2024]))
        );
        $this->assertSame(
            'ALTER TABLE `t` ADD INDEX `i` (`2024` DESC)',
            $dialect->addIndex('t', '', new Index('i', ['columns' => [2024], 'directions' => ['DESC']]))
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbPdoFactoryLoadAdapterInt(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service 123 is not registered');

        (new PdoFactory())->load(['adapter' => 123, 'options' => ['dbname' => ':memory:']]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('mysql')]
    #[Group('pgsql')]
    #[Group('sqlite')]
    public function testDbReferenceIntOptions(): void
    {
        $reference = new Reference(
            'fk',
            [
                'referencedTable'   => 'r',
                'schema'            => 2024,
                'referencedSchema'  => 2024,
                'onDelete'          => 1,
                'onUpdate'          => 1,
                'columns'           => ['a'],
                'referencedColumns' => ['b'],
            ]
        );

        $this->assertEquals(2024, $reference->getSchemaName());
        $this->assertEquals(2024, $reference->getReferencedSchema());
        $this->assertEquals(1, $reference->getOnDelete());
        $this->assertEquals(1, $reference->getOnUpdate());

        $reference = new Reference(
            'fk',
            [
                'referencedTable'   => 'r',
                'referencedSchema'  => 2024,
                'columns'           => ['a'],
                'referencedColumns' => ['b'],
            ]
        );

        $expected = 'ALTER TABLE `t` ADD CONSTRAINT `fk` FOREIGN KEY (`a`) REFERENCES `2024`.`r`(`b`)';
        $this->assertSame($expected, (new Mysql())->addForeignKey('t', '', $reference));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    #[Group('sqlite')]
    public function testDbSqliteUsernamePasswordInt(): void
    {
        $connection = new Sqlite(
            [
                'dbname'   => ':memory:',
                'username' => 123,
                'password' => 456,
            ]
        );

        $this->assertEquals(1, $connection->fetchColumn('SELECT 1'));
    }
}
