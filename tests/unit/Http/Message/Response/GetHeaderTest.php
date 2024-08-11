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

final class GetHeaderTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetHeader(): void
    {
        $data = [
            'cache-control' => ['max-age=0'],
            'accept'        => [Http::CONTENT_TYPE_HTML],
        ];

        $response = new Response(Http::STREAM_MEMORY, 200, $data);

        $expected = [Http::CONTENT_TYPE_HTML];

        $this->assertSame(
            $expected,
            $response->getHeader('accept')
        );


        $this->assertSame(
            $expected,
            $response->getHeader('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Response :: getHeader() - empty headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetHeaderEmptyHeaders(): void
    {
        $response = new Response();

        $this->assertSame(
            [],
            $response->getHeader('empty')
        );
    }
}
