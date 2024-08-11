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

namespace Phalcon\Tests\Unit\Http\Message\Request;

use Phalcon\Http\Message\Request;
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetUriTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: getUri()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetUri(): void
    {
        $query   = 'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag';
        $uri     = new Uri($query);
        $request = new Request('GET', $uri);

        $this->assertSame(
            $uri,
            $request->getUri()
        );
    }
}
