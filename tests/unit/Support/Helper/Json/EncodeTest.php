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

namespace Phalcon\Tests\Unit\Support\Helper\Json;

use InvalidArgumentException;
use Phalcon\Support\Helper\Json\Encode;
use Phalcon\Tests\UnitTestCase;

use const JSON_HEX_TAG;

final class EncodeTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Json :: encode()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperJsonEncode(): void
    {
        $object   = new Encode();
        $data     = [
            'one' => 'two',
            'three',
        ];
        $expected = '{"one":"two","0":"three"}';
        $actual   = $object($data);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\Json :: encode() - exception default options
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperJsonEncodeExceptionDefaultOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Malformed UTF-8 characters, possibly incorrectly encoded"
        );
        $data = pack("H*", 'c32e');
        (new Encode())($data);
    }

    /**
     * Tests Phalcon\Support\Helper\Json :: encode() - exception no options
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperJsonEncodeExceptionNoOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Malformed UTF-8 characters, possibly incorrectly encoded"
        );
        $data = pack("H*", 'c32e');
        (new Encode())($data, JSON_HEX_TAG);
    }
}
