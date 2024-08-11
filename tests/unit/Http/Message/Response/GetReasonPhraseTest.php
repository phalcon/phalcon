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

final class GetReasonPhraseTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: getReasonPhrase()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetReasonPhrase(): void
    {
        $response = new Response();

        $this->assertSame(
            'OK',
            $response->getReasonPhrase()
        );
    }

    /**
     * Tests Phalcon\Http\Message\Response :: getReasonPhrase() - other port
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-09
     */
    public function testHttpMessageResponseGetReasonPhraseOtherPort(): void
    {
        $response = new Response(Http::STREAM_MEMORY, 420);

        $this->assertSame(
            'Method Failure',
            $response->getReasonPhrase()
        );
    }
}
