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

use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Support\Helper\Str\ReduceSlashes;
use Phalcon\Tests\UnitTestCase;

final class ReduceSlashesTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Str :: reduceSlashes()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportHelperStrReduceSlashes(): void
    {
        $object = new ReduceSlashes();

        $expected = 'app/controllers/IndexController';
        $actual   = $object('app/controllers//IndexController');
        $this->assertSame($expected, $actual);

        $expected = 'https://foo/bar/baz/buz';
        $actual   = $object('https://foo//bar/baz/buz');
        $this->assertSame($expected, $actual);

        $expected = Http::STREAM_MEMORY;
        $actual   = $object(Http::STREAM_MEMORY);
        $this->assertSame($expected, $actual);

        $expected = 'http/https';
        $actual   = $object('http//https');
        $this->assertSame($expected, $actual);
    }
}
