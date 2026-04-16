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

use Phalcon\Http\Message\Factories\ResponseFactory;
use Phalcon\Http\Message\Interfaces\ResponseInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class ResponseFactoryTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Factories\ResponseFactory :: createResponse()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesResponseFactoryCreate(): void
    {
        $factory  = new ResponseFactory();
        $response = $factory->createResponse(201, 'Created');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Created', $response->getReasonPhrase());
    }
}
