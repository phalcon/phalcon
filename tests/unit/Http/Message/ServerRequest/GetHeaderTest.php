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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use Phalcon\Http\Message\ServerRequest;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetHeaderTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestGetHeader(): void
    {
        $data    = [
            'Cache-Control' => ['max-age=0'],
            'Accept'        => [Http::CONTENT_TYPE_HTML],
        ];
        $request = new ServerRequest('GET', null, [], Http::STREAM, $data);

        $expected = [Http::CONTENT_TYPE_HTML];
        $actual   = $request->getHeader('accept');
        $this->assertSame($expected, $actual);

        $actual = $request->getHeader('aCCepT');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestGetHeaderEmptyHeaders(): void
    {
        $request = new ServerRequest();

        $expected = [];
        $actual   = $request->getHeader('empty');
        $this->assertSame($expected, $actual);
    }
}
