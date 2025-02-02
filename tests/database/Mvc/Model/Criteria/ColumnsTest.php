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

namespace Phalcon\Tests\Database\Mvc\Model\Criteria;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

final class ColumnsTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: columns()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testMvcModelCriteriaColumns(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);

        $criteria
            ->setModelName(Invoices::class)
            ->columns('inv_id, inv_total')
        ;

        $builder = $criteria->createBuilder();

        $this->assertInstanceOf(Builder::class, $builder);

        $expected = 'SELECT inv_id, inv_total '
            . 'FROM [Phalcon\Tests\Models\Invoices]';
        $actual   = $builder->getPhql();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: columns() - array
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testMvcModelCriteriaColumnsArray(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);

        $criteria
            ->setModelName(Invoices::class)
            ->columns(
                [
                    'id'    => 'inv_id',
                    'total' => 'inv_total',
                ]
            )
        ;

        $builder = $criteria->createBuilder();

        $this->assertInstanceOf(Builder::class, $builder);

        $expected = 'SELECT inv_id AS [id], inv_total AS [total] '
            . 'FROM [Phalcon\Tests\Models\Invoices]';
        $actual   = $builder->getPhql();
        $this->assertEquals($expected, $actual);
    }
}
