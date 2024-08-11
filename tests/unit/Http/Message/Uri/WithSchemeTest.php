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

use InvalidArgumentException;
use Phalcon\Http\Message\Uri;
use Phalcon\Tests\AbstractUnitTestCase;

use function sprintf;

final class WithSchemeTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Uri :: withScheme()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-09
     */
    public function testHttpMessageUriWithScheme(): void
    {
        $query = '%s://phalcon:secret@dev.phalcon.ld:8000/action?param=value#frag';

        $uri = new Uri(
            sprintf($query, 'https')
        );

        $newInstance = $uri->withScheme('http');
        $this->assertNotSame($uri, $newInstance);

        $example = "http";
        $actual  = $newInstance->getScheme();
        $this->assertSame($example, $actual);

        $example = sprintf($query, 'http');
        $actual  = (string)$newInstance;
        $this->assertSame($example, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\Uri :: withScheme() - exception unsupported
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-06-01
     */
    public function testHttpUriWithSchemeExceptionUnsupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Unsupported scheme [ftp]. Scheme must be one of [http, https]'
        );

        $uri = new Uri(
            'https://phalcon:secret@dev.phalcon.ld:8080/action?param=value#frag'
        );

        $uri->withScheme('ftp');
    }
}
