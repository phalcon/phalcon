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

namespace Phalcon\Tests\Unit\Mvc\Model\Criteria;

use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

final class AndWhereTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: andWhere()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testMvcModelCriteriaAndWhere(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);

        $criteria
            ->setModelName(Invoices::class)
            ->where('inv_cst_id = 1')
            ->andWhere('inv_status_flag = :status:', ['status' => 2])
        ;

        $builder = $criteria->createBuilder();

        $this->assertInstanceOf(Builder::class, $builder);

        $expected = 'SELECT [Phalcon\Tests\Models\Invoices].* '
            . 'FROM [Phalcon\Tests\Models\Invoices] '
            . 'WHERE (inv_cst_id = 1) '
            . 'AND (inv_status_flag = :status:)';
        $actual   = $builder->getPhql();
        $this->assertEquals($expected, $actual);
    }
}
