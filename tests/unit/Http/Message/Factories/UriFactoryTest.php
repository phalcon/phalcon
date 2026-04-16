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

namespace Phalcon\Tests\Unit\Http\Message\Factories;

use Phalcon\Http\Message\Factories\UriFactory;
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class UriFactoryTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Factories\UriFactory :: createUri()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesUriFactoryCreate(): void
    {
        $factory = new UriFactory();
        $uri     = $factory->createUri('https://example.com/path?q=1');

        $this->assertInstanceOf(UriInterface::class, $uri);
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('q=1', $uri->getQuery());
    }
}
