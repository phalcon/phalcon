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

use DatabaseTester;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Storage\Exception;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Models\Invoices;

class FromInputCest
{
    use DiTrait;

    /**
     * Executed before each test
     *
     * @param DatabaseTester $I
     *
     * @return void
     */
    public function _before(DatabaseTester $I): void
    {
        try {
            $this->setNewFactoryDefault();
        } catch (Exception $e) {
            $I->fail($e->getMessage());
        }

        $this->setDatabase($I);
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: fromInput()
     *
     * @param DatabaseTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-05
     *
     * @group  mysql
     * @group  sqlite
     */
    public function mvcModelCriteriaFromInputMysql(DatabaseTester $I): void
    {
        $I->wantToTest('Mvc\Model\Criteria - fromInput()');

        $criteria = Criteria::fromInput(
            $this->container,
            Invoices::class,
            [
                'inv_id'          => 1,
                'inv_cst_id'      => 2,
                'inv_status_flag' => 3,
                'inv_title'       => 'title',
                'inv_total'       => 100.10,
                'inv_created_at'  => '2020-12-25 01:02:03',
            ]
        );

        $builder = $criteria->createBuilder();

        if ($I->getDriver() === 'sqlite') {
            $expected = 'SELECT [Phalcon\Tests\Models\Invoices].* '
                . 'FROM [Phalcon\Tests\Models\Invoices] '
                . 'WHERE [inv_id] = :inv_id: '
                . 'AND [inv_cst_id] = :inv_cst_id: '
                . 'AND [inv_status_flag] = :inv_status_flag: '
                . 'AND [inv_title] LIKE :inv_title: '
                . 'AND [inv_total] LIKE :inv_total: '
                . 'AND [inv_created_at] LIKE :inv_created_at:';
        } else {
            $expected = 'SELECT [Phalcon\Tests\Models\Invoices].* '
                . 'FROM [Phalcon\Tests\Models\Invoices] '
                . 'WHERE [inv_id] = :inv_id: '
                . 'AND [inv_cst_id] = :inv_cst_id: '
                . 'AND [inv_status_flag] = :inv_status_flag: '
                . 'AND [inv_title] LIKE :inv_title: '
                . 'AND [inv_total] = :inv_total: '
                . 'AND [inv_created_at] = :inv_created_at:';
        }

        $I->assertEquals($expected, $builder->getPhql());

        if ($I->getDriver() === 'sqlite') {
            $expected = [
                'inv_id'          => 1,
                'inv_cst_id'      => 2,
                'inv_status_flag' => 3,
                'inv_title'       => '%title%',
                'inv_total'       => '%100.1%',
                'inv_created_at'  => '%2020-12-25 01:02:03%',
            ];
        } else {
            $expected = [
                'inv_id'          => 1,
                'inv_cst_id'      => 2,
                'inv_status_flag' => 3,
                'inv_title'       => '%title%',
                'inv_total'       => '100.10',
                'inv_created_at'  => '2020-12-25 01:02:03',
            ];
        }

        $I->assertEquals($expected, $builder->getBindParams());
    }
}
