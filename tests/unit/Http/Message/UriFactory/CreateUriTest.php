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

namespace Phalcon\Tests\Unit\Http\Message\UriFactory;

use Phalcon\Http\Message\Factories\UriFactory;
use Phalcon\Http\Message\Interfaces\UriInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class CreateUriTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\UriFactory :: createUri()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageUriFactoryCreateUri(): void
    {
        $factory = new UriFactory();
        $uri     = $factory->createUri();
        $class   = UriInterface::class;
        $this->assertInstanceOf($class, $uri);
    }
}
