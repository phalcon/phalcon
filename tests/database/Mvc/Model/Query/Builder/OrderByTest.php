<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Database\Mvc\Model\Query\Builder;

use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\RecordsTrait;
use Phalcon\Tests\Models\Invoices;

class OrderByTest extends DatabaseTestCase
{
    use DiTrait;
    use RecordsTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
        $this->setDatabase();
    }

    /**
     * Tests Phalcon\Mvc\Model\Query :: getSql() - Issue 14657
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-20
     * @issue  15411
     *
     * @group  mysql
     * @group  pgsql
     * @group  sqlite
     */
    public function testMvcModelQueryBuilderOrderBy()
    {
        $builder = new Builder(null, $this->container);
        $phql    = $builder
            ->columns('inv_id, inv_title')
            ->addFrom(Invoices::class)
            ->orderBy('inv_title')
            ->getPhql();

        $expected = 'SELECT inv_id, inv_title '
            . 'FROM [Phalcon\Tests\Models\Invoices] '
            . 'ORDER BY inv_title';
        $actual   = $phql;
        $this->assertEquals($expected, $actual);

        $phql = $builder
            ->orderBy('inv_title DESC')
            ->getPhql();

        $expected = 'SELECT inv_id, inv_title '
            . 'FROM [Phalcon\Tests\Models\Invoices] '
            . 'ORDER BY inv_title DESC';
        $actual   = $phql;
        $this->assertEquals($expected, $actual);
    }
}
