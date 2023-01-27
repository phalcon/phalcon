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

use Page\Http;
use Phalcon\Mvc\Micro;
use Phalcon\Tests\Fixtures\Micro\HttpResponseContentMiddleware;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

use function ob_get_clean;
use function ob_start;
use function uniqid;
use function xdebug_get_headers;

class GetSetStatusCodeCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Response :: getStatusCode() / setStatusCode()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-12-24
     */
    public function httpResponseGetSetStatusCode(UnitTester $I)
    {
        $I->wantToTest('Http\Response - getStatusCode() / setStatusCode()');

        $code = Http::CODE_200;
        $response = $this->getResponseObject();
        $response->setStatusCode($code);

        $expected = $code;
        $actual   = $response->getStatusCode();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests the setStatusCode
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-08
     */
    public function testHttpResponseSetStatusCode(UnitTester $I)
    {
        $response = $this->getResponseObject();
        $response->resetHeaders();
        $response->setStatusCode(
            Http::CODE_404,
            Http::NOT_FOUND
        );

        $headers = $response->getHeaders();

        $actual = $headers->has(Http::HTTP_404_NOT_FOUND);
        $I->assertTrue($actual);

        $actual = $headers->get(Http::HTTP_404_NOT_FOUND);
        $I->assertNull($actual);

        $expected = Http::MESSAGE_404_NOT_FOUND;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests the Multiple Status Codes
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/1892
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testMultipleHttpHeaders(UnitTester $I)
    {
        $response = $this->getResponseObject();

        $response->resetHeaders();
        $response->setStatusCode(Http::CODE_200, Http::OK);
        $response->setStatusCode(Http::CODE_404, Http::NOT_FOUND);
        $response->setStatusCode(Http::CODE_409, Http::CONFLICT);

        $headers = $response->getHeaders();

        $actual = $headers->get(Http::HTTP_409_CONFLICT);
        $I->assertNull($actual);

        $expected = Http::MESSAGE_409_CONFLICT;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);
    }

    public function testSetStatusCodeDefaultMessage(UnitTester $I)
    {
        $response = $this->getResponseObject();

        $response->resetHeaders();
        $response->setStatusCode(Http::CODE_103);

        $headers = $response->getHeaders();

        $actual = $headers->get(Http::HTTP_103_EARLY_HINTS);
        $I->assertNull($actual);

        $expected = Http::MESSAGE_103_EARLY_HINTS;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);

        /**
         * 200
         */
        $response->setStatusCode(Http::CODE_200);

        $headers = $response->getHeaders();

        $actual = $headers->get(Http::HTTP_200_OK);
        $I->assertNull($actual);

        $expected = Http::MESSAGE_200_OK;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);

        /**
         * 418
         */
        $response->setStatusCode(Http::CODE_418);

        $headers = $response->getHeaders();

        $actual = $headers->get(Http::HTTP_418_IM_A_TEAPOT);
        $I->assertNull($actual);

        $expected = Http::MESSAGE_418_IM_A_TEAPOT;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);

        /**
         * 418 Custom
         */
        $message = uniqid('mess-');
        $status  = '418 ' . $message;
        $name    = 'HTTP/1.1 ' . $status;
        $response->setStatusCode(Http::CODE_418, $message);

        $headers = $response->getHeaders();

        $actual = $headers->get($name);
        $I->assertNull($actual);

        $expected = $status;
        $actual   = $headers->get(Http::STATUS);
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests the setStatusCode after send
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-08
     */
    public function testHttpResponseSetStatusCodeSend(UnitTester $I)
    {
        $response = $this->getResponseObject();

        $body = ['test' => 123];
        $response
            ->resetHeaders()
            ->setStatusCode(Http::CODE_404)
            ->setContentType(Http::CONTENT_TYPE_JSON, Http::UTF8)
            ->setJsonContent($body, JSON_NUMERIC_CHECK)
        ;

        ob_start();
        $response->send();
        $contents = ob_get_clean();

        $expected = [
            'Status: ' . Http::MESSAGE_404_NOT_FOUND,
            'Content-Type: ' . Http::CONTENT_TYPE_JSON,
        ];
        $actual   = xdebug_get_headers();
        $I->assertSame($expected, $actual);

        $expected = '{"test":123}';
        $actual   = $contents;
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests the setStatusCode after send
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-10-08
     * @todo   enable with MVC
     */
    public function testHttpResponseSetStatusCodeSendMicro(UnitTester $I)
    {
        $I->skipTest('Enable with MVC');
        $application = new Micro($this->container);

        $application->before(new HttpResponseContentMiddleware());
        $application->notFound(
            function () {
                return '404 - handler';
            }
        );
        $application->get(
            "/",
            function () {
                return '200 - "/"';
            }
        );

        ob_start();
        $application->handle("/");
        $contents = ob_get_clean();

        $expected = [
            "Status: 404 Not Found",
            "Content-Type: application/json",
        ];
        $actual   = xdebug_get_headers();
        $I->assertSame($expected, $actual);

        $expected = '{"test":123}';
        $actual   = $contents;
        $I->assertSame($expected, $actual);
    }
}
