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

namespace Phalcon\Tests\Unit\Support\Helper\Str;

use Phalcon\Support\Helper\Str\DirSeparator;
use UnitTester;

use const DIRECTORY_SEPARATOR;
use const PHP_OS;
use const PHP_OS_FAMILY;

/**
 * Class DirSeparatorCest
 *
 * @package Phalcon\Tests\Unit\Support\Helper\Str
 */
class DirSeparatorCest
{
    /**
     * Tests Phalcon\Support\Helper\Str :: dirSeparator()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportHelperStrFolderSeparator(UnitTester $I)
    {
        $I->wantToTest('Support\Helper\Str - dirSeparator()');

        $object = new DirSeparator();

        $expected = $I->convertDirSeparator('/home/phalcon/');
        $actual   = $object('/home/phalcon');
        $I->assertEquals($expected, $actual);

        $expected = $I->convertDirSeparator('/home/phalcon/');
        $actual   = $object('/home/phalcon//');
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Str :: dirSeparator() - empty string
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportHelperStrFolderSeparatorEmptyString(UnitTester $I)
    {
        $I->wantToTest('Support\Helper\Str - dirSeparator() - empty string');
        $fileName = '';
        $object   = new DirSeparator();

        $expected = $I->convertDirSeparator("/");
        $actual   = $object($fileName);
        $I->assertEquals($expected, $actual);
    }
}
