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

use Phalcon\Http\Message\Interfaces\ResponseInterface;
use Phalcon\Http\Message\Response;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Response :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-08
     */
    public function testHttpMessageResponseConstruct(): void
    {
        $response = new Response();
        $class    = ResponseInterface::class;
        $this->assertInstanceOf($class, $response);
    }
}
