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
use UnitTester;

class DirFromFileCest
{
    /**
     * Tests Phalcon\Support\Str :: dirFromFile()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrFolderFromFile(UnitTester $I)
    {
        $I->wantToTest('Support\Str - dirFromFile()');
        $fileName = 'abcdef12345.jpg';
        $object   = new DirFromFile();

        $expected = 'ab/cd/ef/12/3/';
        $actual   = $object($fileName);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Str :: dirFromFile() - empty string
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrFolderFromFileEmptyString(UnitTester $I)
    {
        $I->wantToTest('Support\Str - dirFromFile() - empty string');
        $fileName = '';
        $object   = new DirFromFile();

        $expected = '/';
        $actual   = $object($fileName);
        $I->assertEquals($expected, $actual);
    }
}
