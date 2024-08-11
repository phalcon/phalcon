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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use Phalcon\Http\Message\ServerRequest;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetAttributeTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getAttribute()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-11
     */
    public function testHttpMessageServerRequestGetAttribute(): void
    {
        $request = (new ServerRequest())
            ->withAttribute('one', 'two')
            ->withAttribute('three', 'four')
        ;

        $expected = 'two';
        $actual   = $request->getAttribute('one');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getAttribute() - unknown
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-11
     */
    public function testHttpMessageServerRequestGetAttributeUnknown(): void
    {
        $request = (new ServerRequest())
            ->withAttribute('one', 'two')
            ->withAttribute('three', 'four')
        ;

        $actual = $request->getAttribute('unknown');
        $this->assertNull($actual);
    }
}
