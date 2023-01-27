<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Tests\Unit\Http\Response;

use Codeception\Example;
use Page\Http;
use Phalcon\Events\Event;
use Phalcon\Http\Response\Headers;
use Phalcon\Http\Response\HeadersInterface;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function uniqid;

class HeadersCest extends HttpBase
{
    /**
     * Tests the instance of the object
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-05
     */
    public function testHttpResponseHeadersInstanceOf(UnitTester $I)
    {
        $headers = new Headers();

        $class = Headers::class;
        $I->assertInstanceOf($class, $headers);

        $class = HeadersInterface::class;
        $I->assertInstanceOf($class, $headers);
    }

    /**
     * Tests the get and set of the response headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-05
     */
    public function testHttpResponseHeadersGetSet(UnitTester $I)
    {
        $headers = new Headers();

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML
        );

        $expected = Http::CONTENT_TYPE_HTML;
        $actual   = $headers->get(Http::CONTENT_TYPE);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests the has of the response headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-02
     */
    public function testHttpResponseHeadersHas(UnitTester $I)
    {
        $headers = new Headers();

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML
        );

        $actual = $headers->has(Http::CONTENT_TYPE);
        $I->assertTrue($actual);

        $header = uniqid('header-');
        $actual = $headers->has($header);
        $I->assertFalse($actual);
    }

    /**
     * Tests the set of the response status headers
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/12895
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2017-06-17
     *
     * @dataProvider statusHeaderProvider
     */
    public function shouldSetResponseStatusHeader(
        UnitTester $I,
        Example $example
    ) {
        $headers = new Headers();

        $code = $example['code'];
        $headers->set(Http::STATUS, $code);
        $headers = $I->getProtectedProperty($headers, 'headers');

        $expected = 1;
        $I->assertCount($expected, $headers);

        $actual = isset($headers[Http::STATUS]);
        $I->assertTrue($actual);

        $expected = (string) $code;
        $actual   = $headers[Http::STATUS];
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests the get of the response status headers
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/12895
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2017-06-17
     *
     * @dataProvider statusHeaderProvider
     */
    public function shouldGetResponseStatusHeader(
        UnitTester $I,
        Example $example
    ) {
        $headers = new Headers();

        $code = $example['code'];
        $I->setProtectedProperty(
            $headers,
            'headers',
            [
                Http::STATUS => $code,
            ]
        );

        $expected = $code;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests resetting the response headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-05
     */
    public function testHttpResponseHeadersReset(UnitTester $I)
    {
        $headers = new Headers();

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML
        );

        $headers->reset();

        $actual = $headers->get(Http::CONTENT_TYPE);
        $I->assertEmpty($actual);
    }

    /**
     * Tests removing a response header
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-05
     */
    public function testHttpResponseHeadersRemove(UnitTester $I)
    {
        $headers = new Headers();

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML
        );

        $headers->remove(Http::CONTENT_TYPE);

        $actual = $headers->get(Http::CONTENT_TYPE);
        $I->assertEmpty($actual);
    }

    /**
     * Tests setting a raw response header
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-05
     */
    public function testHttpResponseHeadersRaw(UnitTester $I)
    {
        $headers = new Headers();

        $headers->setRaw(Http::CONTENT_TYPE_HTML_RAW);

        $actual = $headers->get(Http::CONTENT_TYPE);
        $I->assertEmpty($actual);
    }

    /**
     * Tests toArray in response headers
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-05
     */
    public function testHttpResponseHeadersToArray(UnitTester $I)
    {
        $headers = new Headers();

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML
        );

        $expected = [
            Http::CONTENT_TYPE => Http::CONTENT_TYPE_HTML,
        ];

        $actual = $headers->toArray();
        $I->assertSame($expected, $actual);
    }

    /**
     * Test the event response:beforeSendHeaders
     *
     * @author Cameron Hall <me@chall.id.au>
     * @since  2018-11-28
     */
    public function testEventBeforeSendHeaders(UnitTester $I)
    {
        $this->setNewFactoryDefault();

        $eventsManager = $this->container->getShared('eventsManager');

        $eventsManager->attach(
            'response:beforeSendHeaders',
            function (Event $event, $response) {
                return false;
            }
        );

        $response = $this->getResponseObject();
        $response->setEventsManager($eventsManager);

        $actual = $response->sendHeaders();
        $I->assertFalse($actual);
    }

    /**
     * Test the event response:beforeSendHeaders
     *
     * @author Cameron Hall <me@chall.id.au>
     * @since  2018-11-28
     */
    public function testEventAfterSendHeaders(UnitTester $I)
    {
        $eventsManager = $this->getDI()->getShared('eventsManager');

        $eventsManager->attach(
            'response:afterSendHeaders',
            function (Event $event, $response) {
                echo 'some content';
            }
        );

        $response = $this->getResponseObject();

        ob_start();

        $response->setEventsManager($eventsManager);
        $response->sendHeaders();

        $expected = 'some content';
        $actual   = ob_get_clean();
        $I->assertSame($expected, $actual);
    }

    /**
     * @return array[]
     */
    private function statusHeaderProvider(): array
    {
        return [
            [
                'message' => 'Unprocessable Entity',
                'code'    => '422',
            ],

            [
                'message' => 'Moved Permanently',
                'code'    => '301',
            ],

            [
                'message' => 'OK',
                'code'    => '200',
            ],

            [
                'message' => 'Service Unavailable',
                'code'    => '503',
            ],

            [
                'message' => 'Not Found',
                'code'    => '404',
            ],

            [
                'message' => 'Created',
                'code'    => '201',
            ],

            [
                'message' => 'Continue',
                'code'    => '100',
            ],
        ];
    }
}
