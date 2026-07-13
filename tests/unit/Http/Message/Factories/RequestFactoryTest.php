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

use Phalcon\Http\Message\Factories\RequestFactory;
use Phalcon\Http\Message\Interfaces\RequestInterface;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

final class RequestFactoryTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Factories\RequestFactory :: createRequest()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesRequestFactoryCreate(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'https://example.com/path');

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame('GET', $request->getMethod());
    }
}
