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

final class HasHeaderTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: hasHeader()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseHasHeader(): void
    {
        $data = [
            'Accept' => [
                Http::CONTENT_TYPE_HTML,
                'text/json',
            ],
        ];

        $response = new Response(Http::STREAM_MEMORY, 200, $data);

        $this->assertTrue(
            $response->hasHeader('accept')
        );

        $this->assertTrue(
            $response->hasHeader('aCCepT')
        );
    }

    /**
     * Tests Phalcon\Http\Message\Response :: hasHeader() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseHasHeaderEmpty(): void
    {
        $response = new Response();

        $this->assertFalse(
            $response->hasHeader('empty')
        );
    }
}
