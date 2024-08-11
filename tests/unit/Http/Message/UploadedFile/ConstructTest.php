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

namespace Phalcon\Tests\Unit\Http\Message\UploadedFile;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Interfaces\UploadedFileInterface;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

use function fopen;

final class ConstructTest extends AbstractUnitTestCase
{
    public static function getStreamExamples(): array
    {
        return [
            [
                ['array'],
            ],
            [
                true,
            ],
            [
                123.45,
            ],
            [
                123,
            ],
            [
                null,
            ],
            [
                new stdClass(),
            ],
        ];
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileConstruct(): void
    {
        $stream = logsDir(uniqid('test'));

        $file = new UploadedFile($stream, 100);

        $this->assertInstanceOf(UploadedFileInterface::class, $file);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - error
     * exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-18
     */
    public function testHttpMessageUploadedFileConstructErrorException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid error. Must be one of the UPLOAD_ERR_* constants'
        );

        $stream = logsDir(uniqid('test'));

        (new UploadedFile($stream, 100, 100));
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - $resource
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileConstructResource(): void
    {
        $stream = logsDir(uniqid('test'));

        $stream = fopen($stream, 'w+b');
        $file   = new UploadedFile($stream, 100);

        $this->assertInstanceOf(UploadedFileInterface::class, $file);
    }

    /**
     * Tests Phalcon\Http\Message\UploadedFile :: __construct() - stream
     * exception
     *
     * @dataProvider getStreamExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-02-18
     */
    public function testHttpMessageUploadedFileConstructStreamException(
        mixed $stream
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stream or file passed');

        (new UploadedFile($stream, 100));
    }
}
