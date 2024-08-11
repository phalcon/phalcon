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

namespace Phalcon\Tests\Database\Mvc\Model\Criteria;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Storage\Exception;
use Phalcon\Tests\AbstractDatabaseTestCase;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

final class LimitTest extends AbstractDatabaseTestCase
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @return void
     */
    public function setUp(): void
    {
        try {
            $this->setNewFactoryDefault();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: limit()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testMvcModelCriteriaLimit(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);

        $criteria
            ->setModelName(Invoices::class)
            ->limit(10)
        ;

        $builder = $criteria->createBuilder();

        $this->assertInstanceOf(Builder::class, $builder);

        $expected = 'SELECT [Phalcon\Tests\Models\Invoices].* '
            . 'FROM [Phalcon\Tests\Models\Invoices] '
            . 'LIMIT :APL0:';

        $this->assertEquals($expected, $builder->getPhql());
        $this->assertEquals(10, $criteria->getLimit());
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: limit() - offset
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testMvcModelCriteriaLimitOffset(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);

        $criteria
            ->setModelName(Invoices::class)
            ->limit(10, 15)
        ;

        $builder = $criteria->createBuilder();

        $this->assertInstanceOf(Builder::class, $builder);

        $expected = 'SELECT [Phalcon\Tests\Models\Invoices].* '
            . 'FROM [Phalcon\Tests\Models\Invoices] '
            . 'LIMIT :APL0: OFFSET :APL1:';

        $this->assertEquals($expected, $builder->getPhql());

        $expected = [
            'number' => 10,
            'offset' => 15,
        ];

        $this->assertEquals($expected, $criteria->getLimit());
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: limit() - null
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-05-04
     *
     * @group  common
     */
    public function testMvcModelCriteriaNoLimit(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);
        $criteria->setModelName(Invoices::class);

        $builder = $criteria->createBuilder();

        $this->assertInstanceOf(Builder::class, $builder);

        $expected = 'SELECT [Phalcon\Tests\Models\Invoices].* '
            . 'FROM [Phalcon\Tests\Models\Invoices]';

        $this->assertEquals($expected, $builder->getPhql());
        $this->assertEquals(null, $criteria->getLimit());
    }
}
