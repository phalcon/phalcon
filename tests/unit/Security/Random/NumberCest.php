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

namespace Phalcon\Tests\Unit\Security\Random;

use Exception;
use Phalcon\Security\Random;
use UnitTester;

/**
 * Class NumberCest
 *
 * @package Phalcon\Tests\Unit\Security\Random
 */
class NumberCest
{
    /**
     * Tests Phalcon\Security\Random :: number()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function securityRandomNumber(UnitTester $I)
    {
        $I->wantToTest("Security\Random - number()");

        $random = new Random();


        $I->expectThrowable(
            Exception::class,
            function () {
                $randNumber = $random->number();
            }
        );

        $I->expectThrowable(
            Exception::class,
            function () {
                $randNumber = $random->number(-1);
            }
        );


        $maxRand    = 1;
        $randNumber = $random->number($maxRand);

        $I->assertGreaterOrEquals(0, $randNumber);
        $I->assertLessOrEquals($maxRand, $randNumber);


        $maxRand    = 1000000000000000000;
        $randNumber = $random->number($maxRand);

        $I->assertGreaterOrEquals(0, $randNumber);
        $I->assertLessOrEquals($maxRand, $randNumber);


        //Buy lottery ticket if this fails (or fix the bug)
        $I->assertNotEquals(
            $randNumber,
            $random->number($maxRand)
        );
    }
}
