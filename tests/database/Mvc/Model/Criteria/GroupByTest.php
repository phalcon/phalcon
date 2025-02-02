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

final class GroupByTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: groupBy()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group mysql
     */
    public function testMvcModelCriteriaGroupBy(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);

        $criteria
            ->setModelName(Invoices::class)
            ->groupBy('inv_cst_id')
        ;

        $builder = $criteria->createBuilder();

        $this->assertInstanceOf(Builder::class, $builder);

        $expected = 'SELECT [Phalcon\Tests\Models\Invoices].* '
            . 'FROM [Phalcon\Tests\Models\Invoices] '
            . 'GROUP BY [inv_cst_id]';
        $actual   = $builder->getPhql();
        $this->assertEquals($expected, $actual);

        $expected = 'inv_cst_id';
        $actual   = $criteria->getGroupBy();
        $this->assertEquals($expected, $actual);
    }
}
