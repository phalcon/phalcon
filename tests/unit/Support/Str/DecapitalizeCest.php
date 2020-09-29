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

namespace Phalcon\Tests\Unit\Support\Str;

use Phalcon\Support\Str\Decapitalize;
use UnitTester;

class DecapitalizeCest
{
    /**
     * Tests Phalcon\Support\Str :: decapitalize()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrDecapitalize(UnitTester $I)
    {
        $I->wantToTest('Support\Str - decapitalize()');

        $object   = new Decapitalize();
        $source   = 'BeetleJuice';
        $expected = 'beetleJuice';
        $actual   = $object($source);
        $I->assertEquals($expected, $actual);

        $source   = 'BeetleJuice';
        $expected = 'bEETLEJUICE';
        $actual   = $object($source, true);
        $I->assertEquals($expected, $actual);
    }
}
