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

namespace Phalcon\Tests\Unit\Session\Bag;

use Phalcon\Tests\UnitTestCase;
use Phalcon\Session\Bag;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

/**
 * Class ToJsonTest extends UnitTestCase
 *
 * @package Phalcon\Tests\Unit\Session\Bag
 */
final class ToJsonTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Bag :: toJson()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionBagToJson(): void
    {
        $this->setNewFactoryDefault();
        $this->setDiService('sessionStream');
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $collection = new Bag($this->container->get("session"), 'BagTest');

        $collection->init($data);
        $expected = json_encode($data);
        $actual   = $collection->toJson();
        $this->assertEquals($expected, $actual);

        $expected = json_encode($data, JSON_PRETTY_PRINT);
        $actual   = $collection->toJson(JSON_PRETTY_PRINT);
        $this->assertEquals($expected, $actual);
    }
}
