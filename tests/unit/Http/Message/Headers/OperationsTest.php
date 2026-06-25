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

namespace Phalcon\Tests\Unit\Http\Message\Headers;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Headers;
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

final class OperationsTest extends AbstractUnitTestCase
{

    /**
     * Tests Phalcon\Http\Message\Headers :: checkHeaderHost() - without port
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersCheckHeaderHostWithoutPort(): void
    {
        $headers = new Headers();
        $headers->set('host', ['example.com']);

        $uri    = new Uri('https://newhost.com/path');
        $result = $headers->checkHeaderHost($headers, $uri);

        $host = $result->get('Host');
        $this->assertSame(['newhost.com'], $host);
    }
    /**
     * Tests Phalcon\Http\Message\Headers :: checkHeaderHost() - with port
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersCheckHeaderHostWithPort(): void
    {
        $headers = new Headers();
        $headers->set('host', ['example.com']);

        $uri    = new Uri('https://newhost.com:8080/path');
        $result = $headers->checkHeaderHost($headers, $uri);

        $host = $result->get('Host');
        $this->assertSame(['newhost.com:8080'], $host);
    }

    /**
     * Tests Phalcon\Http\Message\Headers :: checkHeaderName() - invalid throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersCheckHeaderNameInvalidThrows(): void
    {
        $headers = new Headers();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header name');
        $headers->checkHeaderName('Invalid Header Name');
    }

    /**
     * Tests Phalcon\Http\Message\Headers :: checkHeaderValue() - invalid chars
     * throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersCheckHeaderValueInvalidCharsThrows(): void
    {
        $headers = new Headers();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header value');
        $headers->checkHeaderValue("value\x00invalid");
    }

    /**
     * Tests Phalcon\Http\Message\Headers :: checkHeaderValue() - invalid
     * non-string throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersCheckHeaderValueNonStringThrows(): void
    {
        $headers = new Headers();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid header value');
        $headers->checkHeaderValue(new \stdClass());
    }

    /**
     * Tests Phalcon\Http\Message\Headers :: getHeaderValue() - empty array throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersGetHeaderValueEmptyArrayThrows(): void
    {
        $headers = new Headers();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid header value: must be a string or array of strings; cannot be an empty array'
        );
        $headers->getHeaderValue([]);
    }

    /**
     * Tests Phalcon\Http\Message\Headers :: processHeaders() - invalid throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersProcessHeadersInvalidThrows(): void
    {
        $headers = new Headers();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Headers needs to be either an array or an instance');
        $headers->processHeaders('invalid-string');
    }

    /**
     * Tests Phalcon\Http\Message\Headers :: processHeaders() - with Headers
     * instance
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageHeadersProcessHeadersWithHeadersInstance(): void
    {
        $source = new Headers();
        $source->set('Content-Type', ['application/json']);

        $headers = new Headers();
        $result  = $headers->processHeaders($source);

        $this->assertSame($source, $result);
    }
}
