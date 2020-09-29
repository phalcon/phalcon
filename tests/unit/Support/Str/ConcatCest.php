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

use Phalcon\Support\Str;
use UnitTester;

class ConcatCest
{
    /**
     * Tests Phalcon\Support\Str :: concat()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportStrConcat(UnitTester $I)
    {
        $I->wantToTest('Support\Str - concat()');
        // Test 1
        $actual   = Str::concat(
            '/',
            '/tmp/',
            '/folder_1/',
            '/folder_2',
            'folder_3/'
        );
        $expected = '/tmp/folder_1/folder_2/folder_3/';
        $I->assertEquals($expected, $actual);

        // Test 2
        $actual   = Str::concat(
            '.',
            '@test.',
            '.test2.',
            '.test',
            '.34'
        );
        $expected = '@test.test2.test.34';
        $I->assertEquals($expected, $actual);
    }
}
