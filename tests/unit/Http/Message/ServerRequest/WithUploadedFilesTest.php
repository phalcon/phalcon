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

final class WithUploadedFilesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withUploadedFiles()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestWithUploadedFiles(): void
    {
        $files       = [
            new UploadedFile(Http::STREAM_MEMORY, 0),
            [
                new UploadedFile(Http::STREAM_MEMORY, 0),
            ],
        ];
        $request     = new ServerRequest();
        $newInstance = $request->withUploadedFiles($files);
        $this->assertNotSame($request, $newInstance);

        $expected = $files;
        $actual   = $newInstance->getUploadedFiles();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withUploadedFiles() -
     * exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageServerRequestWithUploadedFilesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid uploaded file');

        $files   = [
            'something-else',
        ];
        $request = new ServerRequest();
        $request->withUploadedFiles($files);
    }
}
