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

namespace Phalcon\Tests\Unit\Support\Json;

use InvalidArgumentException;
use Phalcon\Support\Json\Decode;
use UnitTester;

class DecodeCest
{
    /**
     * Tests Phalcon\Support\Json :: decode()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportJsonDecode(UnitTester $I)
    {
        $I->wantToTest('Support\Json - decode()');

        $object   = new Decode();
        $data     = '{"one":"two","0":"three"}';
        $expected = [
            'one' => 'two',
            'three',
        ];
        $actual   = $object($data, true);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Json :: decode() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportJsonDecodeException(UnitTester $I)
    {
        $I->wantToTest('Support\Json - decode() - exception');

        $I->expectThrowable(
            new InvalidArgumentException(
                "json_decode error: Control character error, " .
                "possibly incorrectly encoded"
            ),
            function () {
                $data   = '{"one';
                (new Decode())($data);
            }
        );
    }
}
