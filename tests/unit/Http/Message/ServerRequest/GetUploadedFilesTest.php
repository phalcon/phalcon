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

namespace Phalcon\Tests\Unit\Http\Message\ServerRequest;

use InvalidArgumentException;
use Phalcon\Http\Message\ServerRequest;
use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetUploadedFilesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getUploadedFiles()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function testHttpMessageServerRequestGetUploadedFiles(): void
    {
        $files   = [
            new UploadedFile(Http::STREAM_MEMORY, 0),
            new UploadedFile(Http::STREAM_MEMORY, 0),
        ];
        $request = new ServerRequest(
            'GET',
            null,
            [],
            Http::STREAM,
            [],
            [],
            [],
            $files
        );

        $expected = $files;
        $actual   = $request->getUploadedFiles();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getUploadedFiles() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function testHttpMessageServerRequestGetUploadedFilesEmpty(): void
    {
        $request = new ServerRequest();

        $actual = $request->getUploadedFiles();
        $this->assertEmpty($actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getUploadedFiles() -
     * exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function testHttpMessageServerRequestGetUploadedFilesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uploaded file');

        $files   = [
            'something-else',
        ];
        $request = new ServerRequest(
            'GET',
            null,
            [],
            Http::STREAM,
            [],
            [],
            [],
            $files
        );

        $request->getUploadedFiles();
    }
}
