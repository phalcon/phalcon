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

use Phalcon\Support\Str\DirFromFile;
use Phalcon\Support\Str\DirSeparator;
use UnitTester;

class DirSeparatorCest
{
    /**
     * Tests Phalcon\Support\Str :: dirSeparator()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrFolderSeparator(UnitTester $I)
    {
        $I->wantToTest('Support\Str - dirSeparator()');

        $object   = new DirSeparator();

        $expected = '/home/phalcon/';
        $actual   = $object('/home/phalcon');
        $I->assertEquals($expected, $actual);

        $expected = '/home/phalcon/';
        $actual   = $object('/home/phalcon//');
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Str :: dirSeparator() - empty string
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrFolderSeparatorEmptyString(UnitTester $I)
    {
        $I->wantToTest('Support\Str - dirSeparator() - empty string');
        $fileName = '';
        $object   = new DirSeparator();

        $expected = '/';
        $actual   = $object($fileName);
        $I->assertEquals($expected, $actual);
    }
}
