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

namespace Phalcon\Tests\Unit\Crypt;

use Phalcon\Crypt\Crypt;
use Phalcon\Crypt\Exception;
use UnitTester;

use function uniqid;

/**
 * Class GetSetHashAlgoCest
 *
 * @package Phalcon\Tests\Unit\Crypt
 */
class GetSetHashAlgoCest
{
    /**
     * Tests Phalcon\Crypt\Crypt :: getHashAlgo() / setHashAlgo()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptGetSetHashAlgo(UnitTester $I)
    {
        $I->wantToTest('Crypt - getHashAlgo() / setHashAlgo()');

        $cipher = 'blowfish';
        $crypt = new Crypt();
        $crypt->setHashAlgo($cipher);

        $I->assertEquals($cipher, $crypt->getHashAlgo());
    }

    /**
     * Tests Phalcon\Crypt\Crypt :: setHashAlgo() - unknown
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptGetSetHashAlgoUnknown(UnitTester $I)
    {
        $I->wantToTest('Crypt - setHashAlgo() - unknown');
        $I->expectThrowable(
            new Exception(
                'The hash algorithm "xxx-yyy-zzz" is not supported on this system.'
            ),
            function () {
                $crypt = new Crypt();

                $crypt->setHashAlgo('xxx-yyy-zzz');
            }
        );
    }
}
