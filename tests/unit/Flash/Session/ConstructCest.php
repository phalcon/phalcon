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

namespace Phalcon\Tests\Unit\Flash\Session;

use Phalcon\Flash\FlashInterface;
use Phalcon\Flash\Session;
use UnitTester;

/**
 * Class ConstructCest
 *
 * @package Phalcon\Tests\Unit\Flash\Session
 */
class ConstructCest
{
    /**
     * Tests Phalcon\Flash\Session :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function flashSessionConstruct(UnitTester $I)
    {
        $I->wantToTest('Flash\Session - __construct()');


        $flash = new Session();
        $I->assertInstanceOf(FlashInterface::class, $flash);
    }
}
