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

namespace Phalcon\Tests\Unit\Http\Response\Headers;

use Phalcon\Http\Response\Headers;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class SendTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Response\Headers :: send()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-08
     */
    public function testHttpResponseHeadersSend(): void
    {
        $headers = new Headers();

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML_CHARSET
        );
        $headers->set(
            Http::CONTENT_ENCODING,
            Http::CONTENT_ENCODING_GZIP
        );

        $actual = $headers->send();
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Http\Response\Headers :: send() - twice
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-04-22
     * @issue  15334
     */
    public function testHttpResponseHeadersSendTwice(): void
    {
        $headers = new Headers();

        $headers->set(
            Http::CONTENT_TYPE,
            Http::CONTENT_TYPE_HTML_CHARSET
        );
        $headers->set(
            Http::CONTENT_ENCODING,
            Http::CONTENT_ENCODING_GZIP
        );

        $actual = $headers->isSent();
        $this->assertFalse($actual);

        $actual = $headers->send();
        $this->assertTrue($actual);

        $actual = $headers->isSent();
        $this->assertTrue($actual);

        $actual = $headers->send();
        $this->assertFalse($actual);
    }
}
