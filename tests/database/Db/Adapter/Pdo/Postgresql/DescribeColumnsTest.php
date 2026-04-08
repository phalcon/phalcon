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

namespace Phalcon\Tests\Database\Db\Adapter\Pdo\Postgresql;

use Phalcon\Db\Column;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;

use function env;

final class DescribeColumnsTest extends AbstractDatabaseTestCase
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
     * Tests Phalcon\Db\Adapter\Pdo\Postgresql :: describeColumns()
     *
     * @issue  https://github.com/phalcon/phalcon-devtools/issues/853
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-09-28
     * @group  pgsql
     */
    public function testDbAdapterPdoPostgresqlDescribeColumns(): void
    {
        $db = $this->container->get('db');

        $expected = $this->getExpectedColumns();

        $actual = $db->describeColumns('co_invoices');
        $this->assertEquals($expected, $actual);

        $actual = $db->describeColumns('co_invoices', env('DATA_POSTGRES_SCHEMA'));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array<int, Column>
     */
    private function getExpectedColumns(): array
    {
        return [
            0 => new Column(
                'inv_id',
                [
                    'type'          => 0,
                    'typeReference' => -1,
                    'typeValues'    => [],
                    'isNumeric'     => true,
                    'size'          => 0,
                    'scale'         => 0,
                    'default'       => "nextval('co_invoices_inv_id_seq'::regclass)",
                    'unsigned'      => false,
                    'notNull'       => true,
                    'primary'       => true,
                    'autoIncrement' => true,
                    'first'         => true,
                    'after'         => '',
                    'bindType'      => 1,
                ]
            ),
            1 => new Column(
                'inv_cst_id',
                [
                    'type'          => 0,
                    'typeReference' => -1,
                    'typeValues'    => [],
                    'isNumeric'     => true,
                    'size'          => 0,
                    'scale'         => 0,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'inv_id',
                    'bindType'      => 1,
                ]
            ),
            2 => new Column(
                'inv_status_flag',
                [
                    'type'          => 22,
                    'typeReference' => -1,
                    'typeValues'    => [],
                    'isNumeric'     => true,
                    'size'          => 0,
                    'scale'         => 0,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'inv_cst_id',
                    'bindType'      => 1,
                ]
            ),
            3 => new Column(
                'inv_title',
                [
                    'type'          => 2,
                    'typeReference' => -1,
                    'typeValues'    => [],
                    'isNumeric'     => false,
                    'size'          => 100,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'inv_status_flag',
                    'bindType'      => 2,
                ]
            ),
            4 => new Column(
                'inv_total',
                [
                    'type'          => 3,
                    'typeReference' => -1,
                    'typeValues'    => [],
                    'isNumeric'     => true,
                    'size'          => 10,
                    'scale'         => 0,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'inv_title',
                    'bindType'      => 32,
                ]
            ),
            5 => new Column(
                'inv_created_at',
                [
                    'type'          => 17,
                    'typeReference' => -1,
                    'typeValues'    => [],
                    'isNumeric'     => false,
                    'size'          => 0,
                    'default'       => null,
                    'unsigned'      => false,
                    'notNull'       => false,
                    'autoIncrement' => false,
                    'first'         => false,
                    'after'         => 'inv_total',
                    'bindType'      => 2,
                ]
            ),
        ];
    }
}
