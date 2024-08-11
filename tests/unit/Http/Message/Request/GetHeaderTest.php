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

namespace Phalcon\Tests\Unit\Http\Message\Request;

use Phalcon\Http\Message\Request;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetHeaderTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetHeader(): void
    {
        $data = [
            'Cache-Control' => ['max-age=0'],
            'Accept'        => [Http::CONTENT_TYPE_HTML],
        ];

        $request = new Request(
            'GET',
            null,
            Http::STREAM_MEMORY,
            $data
        );

        $expected = [Http::CONTENT_TYPE_HTML];

        $this->assertSame(
            $expected,
            $request->getHeader('accept')
        );


        $this->assertSame(
            $expected,
            $request->getHeader('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Request :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetHeaderEmptyHeaders(): void
    {
        $request = new Request();

        $this->assertSame(
            [],
            $request->getHeader('empty')
        );
    }
}
