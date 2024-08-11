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

namespace Phalcon\Tests\Unit\Http\Message\Response;

use Phalcon\Http\Message\Response;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetHeaderLineTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: getHeaderLine()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetHeaderLine(): void
    {
        $data = [
            'accept' => [
                Http::CONTENT_TYPE_HTML,
                'text/json',
            ],
        ];

        $response = new Response(Http::STREAM_MEMORY, 200, $data);

        $expected = 'text/html,text/json';

        $this->assertSame(
            $expected,
            $response->getHeaderLine('accept')
        );

        $this->assertSame(
            $expected,
            $response->getHeaderLine('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Response :: getHeaderLine() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetHeaderLineEmpty(): void
    {
        $response = new Response();

        $this->assertSame(
            '',
            $response->getHeaderLine('accept')
        );
    }
}
