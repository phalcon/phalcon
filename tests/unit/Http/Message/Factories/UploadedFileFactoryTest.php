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

use Phalcon\Http\Message\Factories\StreamFactory;
use Phalcon\Http\Message\Factories\UploadedFileFactory;
use Phalcon\Http\Message\Interfaces\UploadedFileInterface;
use Phalcon\Tests\AbstractUnitTestCase;

final class UploadedFileFactoryTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Factories\UploadedFileFactory ::
     * createUploadedFile()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageFactoriesUploadedFileFactoryCreate(): void
    {
        $streamFactory = new StreamFactory();
        $stream        = $streamFactory->createStream('file contents');

        $factory      = new UploadedFileFactory();
        $uploadedFile = $factory->createUploadedFile(
            $stream,
            13,
            0,
            'test.txt',
            'text/plain'
        );

        $this->assertInstanceOf(UploadedFileInterface::class, $uploadedFile);
        $this->assertSame(13, $uploadedFile->getSize());
        $this->assertSame(0, $uploadedFile->getError());
        $this->assertSame('test.txt', $uploadedFile->getClientFilename());
        $this->assertSame('text/plain', $uploadedFile->getClientMediaType());
    }
}
