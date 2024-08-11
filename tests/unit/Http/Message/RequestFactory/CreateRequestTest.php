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

namespace Phalcon\Tests\Unit\Http\Message\RequestFactory;

use Phalcon\Http\Message\Factories\RequestFactory;
use Phalcon\Http\Message\Interfaces\RequestInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class CreateRequestTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\RequestFactory :: createRequest()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageRequestFactoryCreateRequest(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'https://dev.phalcon.ld');

        $this->assertInstanceOf(
            RequestInterface::class,
            $request
        );
    }
}
