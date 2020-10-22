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

namespace Phalcon\Tests\Integration\Session\Bag;

use IntegrationTester;
use Phalcon\Session\Bag;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

/**
 * Class UnserializeCest
 *
 * @package Phalcon\Tests\Integration\Session\Bag
 */
class UnserializeCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Bag :: unserialize()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionBagUnserialize(IntegrationTester $I)
    {
        $I->wantToTest('Session\Bag - unserialize()');

        $this->setNewFactoryDefault();
        $this->setDiService('sessionStream');
        $data = [
            'one'   => 'two',
            'three' => 'four',
            'five'  => 'six',
        ];

        $serialized = serialize($data);
        $collection = new Bag('BagTest');

        $collection->unserialize($serialized);
        $I->assertEquals($data, $collection->toArray());
    }
}
