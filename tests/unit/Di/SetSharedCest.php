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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Di\Di;
use Phalcon\Escaper\Escaper;
use UnitTester;

/**
 * Class SetSharedCest
 *
 * @package Phalcon\Tests\Unit\Di
 */
class SetSharedCest
{
    /**
     * Unit Tests Phalcon\Di :: setShared()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function diSetShared(UnitTester $I)
    {
        $I->wantToTest('Di - setShared()');

        $container = new Di();
        $container->setShared('escaper', Escaper::class);

        // check shared service
        $actual = $container->getService('escaper');
        $I->assertTrue($actual->isShared());

        $actual = $container->getShared('escaper');
        $I->assertInstanceOf(Escaper::class, $actual);
    }
}
