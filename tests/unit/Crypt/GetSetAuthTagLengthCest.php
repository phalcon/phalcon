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
use UnitTester;

use function uniqid;

class GetSetAuthTagLengthCest
{
    /**
     * Unit Tests Phalcon\Crypt\Crypt :: getAuthTagLength()/setAuthTagLength()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cryptGetSetAuthTagLength(UnitTester $I)
    {
        $I->wantToTest('Crypt - getAuthTagLength()/setAuthTagLength()');

        $crypt = new Crypt();

        $data = 1234;
        $crypt->setAuthTagLength($data);

        $expected = $data;
        $actual   = $crypt->getAuthTagLength();
        $I->assertEquals($expected, $actual);
    }
}
