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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequestFactory;

use Phalcon\Http\Message\Factories\ServerRequestFactory;
use Phalcon\Http\Message\Interfaces\ServerRequestInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class CreateServerRequestTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequestFactory :: createServerRequest()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-09
     */
    public function testHttpMessageServerRequestFactoryCreateServerRequest(): void
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '');
        $class   = ServerRequestInterface::class;
        $this->assertInstanceOf($class, $request);
    }
}
