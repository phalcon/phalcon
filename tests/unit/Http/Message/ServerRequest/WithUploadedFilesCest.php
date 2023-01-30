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
use Page\Http;
use Phalcon\Http\Message\ServerRequest;
use Phalcon\Http\Message\UploadedFile;
use UnitTester;

class WithUploadedFilesCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withUploadedFiles()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithUploadedFiles(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - withUploadedFiles()');
        $files       = [
            new UploadedFile(Http::STREAM_MEMORY, 0),
            [
                new UploadedFile(Http::STREAM_MEMORY, 0),
            ],
        ];
        $request     = new ServerRequest();
        $newInstance = $request->withUploadedFiles($files);
        $I->assertNotSame($request, $newInstance);

        $expected = $files;
        $actual   = $newInstance->getUploadedFiles();
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: withUploadedFiles() -
     * exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function httpMessageServerRequestWithUploadedFilesException(
        UnitTester $I
    ) {
        $I->wantToTest(
            'Http\Message\ServerRequest - withUploadedFiles() - exception'
        );
        $I->expectThrowable(
            new InvalidArgumentException('Invalid uploaded file'),
            function () use ($I) {
                $files       = [
                    'something-else',
                ];
                $request     = new ServerRequest();
                $newInstance = $request->withUploadedFiles($files);
            }
        );
    }
}
