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

namespace Phalcon\Tests\Unit\Http\Response;

use Phalcon\Http\Response;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

use function json_encode;

use const JSON_HEX_TAG;

final class SetJsonContentTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Response :: setJsonContent()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-12-07
     */
    public function testHttpResponseSetJsonContent(): void
    {
        $content = [
            'sentence' => 'it\'s a "city"',
            'word'     => '<h1>city</h1>',
        ];

        $response = new Response();
        $response->setJsonContent($content);

        // Check content
        $expected = json_encode($content);
        $actual   = $response->getContent();
        $this->assertSame($expected, $actual);

        // Check Header
        $expected = Http::CONTENT_TYPE_JSON;
        $actual   = $response->getHeaders()->get(Http::CONTENT_TYPE);
        $this->assertSame($expected, $actual);

        // With option
        $response = new Response();
        $response->setJsonContent($content, JSON_HEX_TAG);

        $expected = json_encode($content, JSON_HEX_TAG);
        $actual   = $response->getContent();
        $this->assertSame($expected, $actual);
    }
}
