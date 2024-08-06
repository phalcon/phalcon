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
use Phalcon\Tests\DatabaseTestCase;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

final class BindTest extends DatabaseTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->setNewFactoryDefault();
    }

    /**
     * Tests Phalcon\Mvc\Model\Criteria :: bind()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-01
     *
     * @group  common
     */
    public function testMvcModelCriteriaBind(): void
    {
        $criteria = new Criteria();
        $criteria->setDI($this->container);

        $criteria->bind(
            [
                'one' => 1,
                'two' => true,
            ]
        );

        $actual = $criteria->getParams();
        $this->assertArrayHasKey('bind', $actual);

        $expected = [
            'one' => 1,
            'two' => true,
        ];
        $actual   = $actual['bind'];
        $this->assertEquals($expected, $actual);

        $criteria->bind(
            [
                'three' => 77,
                'four'  => 'text',
            ],
            true
        );

        $actual = $criteria->getParams();
        $this->assertArrayHasKey('bind', $actual);

        $expected = [
            'one'   => 1,
            'two'   => true,
            'three' => 77,
            'four'  => 'text',
        ];
        $actual   = $actual['bind'];
        $this->assertEquals($expected, $actual);
    }
}
