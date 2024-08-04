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

namespace Phalcon\Tests\Unit\Http\Message\ResponseFactory;

use Phalcon\Http\Message\Factories\ResponseFactory;
use Phalcon\Http\Message\Interfaces\ResponseInterface;
use Phalcon\Tests\UnitTestCase;

final class CreateResponseTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ResponseFactory :: createResponse()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageResponseFactoryCreateResponse(): void
    {
        $factory  = new ResponseFactory();
        $response = $factory->createResponse();
        $class    = ResponseInterface::class;
        $this->assertInstanceOf($class, $response);
    }
}
