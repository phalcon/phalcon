<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Http;

use Phalcon\Http\Cookie;
use Phalcon\Http\Request;
use Phalcon\Http\Request\Exceptions\SanitizerNotFound;
use Phalcon\Http\Request\File;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Headers;
use Phalcon\Tests\Unit\Http\Helper\AbstractHttpBase;
use PHPUnit\Framework\Attributes\BackupGlobals;

use function array_keys;
use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string. cphalcon returns the value with no
 * change, thus the tests use assertEquals for the return values.
 *
 * @issue https://github.com/phalcon/phalcon/issues/886
 */
#[BackupGlobals(true)]
final class WeakModeIntValuesTest extends AbstractHttpBase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testHttpCookieIntValue(): void
    {
        $cookie = new Cookie('weak-int', 5);
        $cookie->setDI($this->container);
        $cookie->useEncryption(false);

        $this->assertSame($cookie, $cookie->send());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testHttpRequestFileIntFields(): void
    {
        $file = new File(
            [
                'name'     => 123,
                'type'     => 456,
                'tmp_name' => 789,
                'size'     => 1,
                'error'    => 0,
            ]
        );

        $this->assertSame('123', $file->getName());
        $this->assertSame('456', $file->getType());
        $this->assertSame('789', $file->getTempName());
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/898
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHttpRequestGetHeadersIntServerKey(): void
    {
        $_SERVER[5]               = 'five';
        $_SERVER['HTTP_WEAK_INT'] = 'yes';

        $headers = (new Request())->getHeaders();

        $this->assertSame('yes', $headers['Weak-Int']);
        $this->assertNotContains('five', $headers);
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/898
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHttpRequestGetUploadedFilesIntFieldName(): void
    {
        $_FILES = [
            5 => [
                'name'     => 'a.txt',
                'type'     => 'text/plain',
                'tmp_name' => '',
                'size'     => 1,
                'error'    => 0,
            ],
            6 => [
                'name'     => ['b.txt'],
                'type'     => ['text/plain'],
                'tmp_name' => [''],
                'size'     => [1],
                'error'    => [0],
            ],
        ];

        $files = (new Request())->getUploadedFiles(false, true);

        $this->assertSame([5, '6.0'], array_keys($files));
        $this->assertSame('5', $files[5]->getKey());
        $this->assertSame('b.txt', $files['6.0']->getName());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testHttpRequestServerIntEntry(): void
    {
        $_SERVER['REQUEST_TIME'] = 1700000000;

        $request = new Request();

        $this->assertEquals(1700000000, $request->getServer('REQUEST_TIME'));
        $this->assertEquals(1700000000, $request->getHeader('REQUEST_TIME'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testHttpRequestSetParameterFiltersInt(): void
    {
        $request = new Request();
        $request->setDI($this->container);

        $this->assertSame($request, $request->setParameterFilters('x', ['trim'], [1]));

        $this->expectException(SanitizerNotFound::class);
        $this->expectExceptionMessage("Sanitizer '123' does not exist in the filter locator");

        $request->setParameterFilters('x', [123]);
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/898
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHttpResponseHeadersSendRawIntName(): void
    {
        $headers = new Headers();
        $headers->setRaw('5');

        /**
         * cphalcon gives a warning for an int header name
         */
        set_error_handler(
            function (): bool {
                return true;
            }
        );

        try {
            $actual = $headers->send();
        } finally {
            restore_error_handler();
        }

        $this->assertTrue($actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testHttpResponseIntValues(): void
    {
        $response = new Response();
        $response->setFileToSend(__FILE__, "caf\xe9.txt");

        $this->assertContains(
            "Content-Disposition: attachment; filename=caf%E9.txt; filename*=''caf%E9.txt",
            array_keys($response->getHeaders()->toArray())
        );

        $headers = new Headers();
        $headers->set('1', 'one');

        $response = new Response();
        $response->setHeaders($headers);

        $this->assertSame('one', $response->getHeaders()->get('1'));
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/898
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testHttpResponseSetStatusCodeIntHeaderName(): void
    {
        $response = new Response();
        $response->setHeader('123', 'x');
        $response->setStatusCode(200);

        $this->assertSame(
            [
                123               => 'x',
                'HTTP/1.1 200 OK' => null,
                'Status'          => '200 OK',
            ],
            $response->getHeaders()->toArray()
        );
    }
}
