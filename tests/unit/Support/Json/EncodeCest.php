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
use Phalcon\Support\Json\Encode;
use UnitTester;

class EncodeCest
{
    /**
     * Tests Phalcon\Support\Json :: encode()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportJsonEncode(UnitTester $I)
    {
        $I->wantToTest('Support\Json - encode()');

        $object   = new Encode();
        $data     = [
            'one' => 'two',
            'three',
        ];
        $expected = '{"one":"two","0":"three"}';
        $actual   = $object($data);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Json :: encode() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportJsonEncodeException(UnitTester $I)
    {
        $I->wantToTest('Support\Json - encode() - exception');

        $I->expectThrowable(
            new InvalidArgumentException(
                "json_encode error: Malformed UTF-8 characters, " .
                "possibly incorrectly encoded"
            ),
            function () {
                $data   = pack("H*", 'c32e');
                (new Encode())($data);
            }
        );
    }
}
