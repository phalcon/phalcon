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

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Factories\StreamFactory;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class StreamFactoryTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Factories\StreamFactory :: createStream()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesStreamFactoryCreateStream(): void
    {
        $factory = new StreamFactory();
        $stream  = $factory->createStream('hello world');

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame('hello world', (string) $stream);
    }

    /**
     * Tests Phalcon\Http\Message\Factories\StreamFactory :: createStreamFromFile()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesStreamFactoryCreateStreamFromFile(): void
    {
        $factory = new StreamFactory();
        $stream  = $factory->createStreamFromFile('php://memory', 'r+b');

        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    /**
     * Tests Phalcon\Http\Message\Factories\StreamFactory :: createStreamFromResource()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesStreamFactoryCreateStreamFromResource(): void
    {
        $factory  = new StreamFactory();
        $resource = fopen('php://memory', 'r+b');
        $stream   = $factory->createStreamFromResource($resource);

        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    /**
     * Tests Phalcon\Http\Message\Factories\StreamFactory ::
     * createStreamFromResource() - invalid resource throws
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesStreamFactoryCreateStreamFromResourceInvalidThrows(): void
    {
        $factory = new StreamFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream provided');
        $factory->createStreamFromResource('not-a-resource');
    }
}
