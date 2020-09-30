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
use JsonException;
use Phalcon\Support\Json\Encode;
use UnitTester;

use const JSON_HEX_TAG;

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
     * Tests Phalcon\Support\Json :: encode() - exception default options
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportJsonEncodeExceptionDefaultOptions(UnitTester $I)
    {
        $I->wantToTest('Support\Json - encode() - exception default options');

        $I->expectThrowable(
            new JsonException(
                'Malformed UTF-8 characters, possibly incorrectly encoded',
                5
            ),
            function () {
                $data   = pack("H*", 'c32e');
                (new Encode())($data);
            }
        );
    }

    /**
     * Tests Phalcon\Support\Json :: encode() - exception no options
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function supportJsonEncodeExceptionNoOptions(UnitTester $I)
    {
        $I->wantToTest('Support\Json - encode() - exception no options');

        $I->expectThrowable(
            new JsonException(
                'Malformed UTF-8 characters, possibly incorrectly encoded',
                5
            ),
            function () {
                $data   = pack("H*", 'c32e');
                (new Encode())($data, JSON_HEX_TAG);
            }
        );
    }
}
