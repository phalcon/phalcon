<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Db\Adapter\Pdo;

use Phalcon\Db\Column;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;

final class DbDescribePostgresqlTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Db :: Postgresql - describeColumns()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @group  pgsql
     */
    public function testDbPostgresqlDescribeColumns(): void
    {
        $db = $this->container->get('db');

        $expected = $this->getExpectedColumnsPostgresql();

        $this->assertEquals($expected, $db->describeColumns('personas'));
        $this->assertEquals($expected, $db->describeColumns('personas', 'public'));
    }

    /**
     * Tests Phalcon\Db :: Postgresql - describeIndexes()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @group  pgsql
     */
    public function testDbPostgresqlDescribeIndexes(): void
    {
        /**
         * @todo Check the references (SQL dump file)
         */
        $this->markTestSkipped('Need implementation - index data not yet defined');
    }

    /**
     * Tests Phalcon\Db :: Postgresql - describeReferences()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     * @group  pgsql
     */
    public function testDbPostgresqlDescribeReferences(): void
    {
        /**
         * @todo Check the references (SQL dump file)
         */
        $this->markTestSkipped('Need implementation - references data not yet defined');
    }

    /**
     * @return array<int, Column>
     */
    private function getExpectedColumnsPostgresql(): array
    {
        return [
            0  => new Column(
                'cedula',
                [
                    'type'          => 5,
                    'isNumeric'     => false,
                    'size'          => 15,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => true,
                    'autoIncrement' => false,
                    'primary'       => true,
                    'first'         => true,
                    'after'         => null,
                    'bindType'      => 2,
                ]
            ),
            1  => new Column(
                'tipo_documento_id',
                [
                    'type'          => 0,
                    'isNumeric'     => true,
                    'size'          => 0,
                    'scale'         => 0,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => true,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'cedula',
                    'bindType'      => 1,
                ]
            ),
            2  => new Column(
                'nombres',
                [
                    'type'          => 2,
                    'isNumeric'     => false,
                    'size'          => 100,
                    'default'       => '',
                    'unsigned'      => false,
                    'notNull'       => true,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'tipo_documento_id',
                    'bindType'      => 2,
                ]
            ),
            3  => new Column(
                'telefono',
                [
                    'type'          => 2,
                    'isNumeric'     => false,
                    'size'          => 20,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'nombres',
                ]
            ),
            4  => new Column(
                'direccion',
                [
                    'type'          => 2,
                    'isNumeric'     => false,
                    'size'          => 100,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'telefono',
                    'bindType'      => 2,
                ]
            ),
            5  => new Column(
                'email',
                [
                    'type'          => 2,
                    'isNumeric'     => false,
                    'size'          => 50,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'direccion',
                    'bindType'      => 2,
                ]
            ),
            6  => new Column(
                'fecha_nacimiento',
                [
                    'type'          => 1,
                    'isNumeric'     => false,
                    'size'          => 0,
                    'default'       => '1970-01-01',
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'email',
                    'bindType'      => 2,
                ]
            ),
            7  => new Column(
                'ciudad_id',
                [
                    'type'          => 0,
                    'isNumeric'     => true,
                    'size'          => 0,
                    'scale'         => 0,
                    'default'       => '0',
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'fecha_nacimiento',
                    'bindType'      => 1,
                ]
            ),
            8  => new Column(
                'creado_at',
                [
                    'type'          => 1,
                    'isNumeric'     => false,
                    'size'          => 0,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'ciudad_id',
                    'bindType'      => 2,
                ]
            ),
            9  => new Column(
                'cupo',
                [
                    'type'          => 3,
                    'isNumeric'     => true,
                    'size'          => 16,
                    'scale'         => 0,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => true,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'creado_at',
                    'bindType'      => 32,
                ]
            ),
            10 => new Column(
                'estado',
                [
                    'type'          => 5,
                    'isNumeric'     => false,
                    'size'          => 1,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => true,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'cupo',
                    'bindType'      => 2,
                ]
            ),
        ];
    }
}
