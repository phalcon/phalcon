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

final class GetRequestTargetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Request :: getRequestTarget()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestGetRequestTarget(): void
    {
        $uri     = new Uri(
            'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag'
        );
        $request = new Request('GET', $uri);

        $this->assertSame(
            '/action?param=value',
            $request->getRequestTarget()
        );
    }
}
