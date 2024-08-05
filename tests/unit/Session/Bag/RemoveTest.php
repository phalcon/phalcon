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
 * Class RemoveTest extends UnitTestCase
 *
 * @package Phalcon\Tests\Unit\Session\Bag
 */
final class RemoveTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Bag :: remove()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSessionBagRemove(): void
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
        $this->assertEquals($data, $collection->toArray());

        $collection->remove('five');
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $this->assertEquals($expected, $collection->toArray());

        $collection->remove('FIVE');
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $this->assertEquals($expected, $collection->toArray());

        $collection->init($data);

        unset($collection['five']);

        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $this->assertEquals($expected, $collection->toArray());

        $collection->init($data);
        $collection->offsetUnset('five');
        $expected = [
            'one'   => 'two',
            'three' => 'four',
        ];
        $this->assertEquals($expected, $collection->toArray());
    }
}
