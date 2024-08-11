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

namespace Phalcon\Tests\Unit\Http\Message\Uri;

use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

final class ToStringTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Uri :: __toString()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageUriToString(): void
    {
        $query = 'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag';
        $uri   = new Uri($query);

        $this->assertSame($query, (string)$uri);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __toString() - path many slashes
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-06-01
     */
    public function testHttpUriToStringPathManySlashes(): void
    {
        $uri = new Uri('https://dev.phalcon.ld');

        $newInstance = $uri->withPath('///action/reaction');
        $expected    = 'https://dev.phalcon.ld/action/reaction';
        $actual      = $newInstance->__toString();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: __toString() - path no lead slash
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-07
     */
    public function testHttpUriToStringPathNoLeadSlash(): void
    {
        $uri = new Uri('https://dev.phalcon.ld');

        $newInstance = $uri->withPath('action/reaction');
        $expected    = 'https://dev.phalcon.ld/action/reaction';
        $actual      = $newInstance->__toString();
        $this->assertSame($expected, $actual);
    }
}
