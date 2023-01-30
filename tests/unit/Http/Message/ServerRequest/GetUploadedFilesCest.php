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

class GetUploadedFilesCest
{
    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getUploadedFiles()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function httpMessageServerRequestGetUploadedFiles(UnitTester $I)
    {
        $I->wantToTest('Http\Message\ServerRequest - getUploadedFiles()');
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
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getUploadedFiles() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function httpMessageServerRequestGetUploadedFilesEmpty(UnitTester $I)
    {
        $I->wantToTest(
            'Http\Message\ServerRequest - getUploadedFiles() - empty'
        );
        $request = new ServerRequest();

        $actual = $request->getUploadedFiles();
        $I->assertEmpty($actual);
    }

    /**
     * Tests Phalcon\Http\Message\ServerRequest :: getUploadedFiles() -
     * exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-03-03
     */
    public function httpMessageServerRequestGetUploadedFilesException(
        UnitTester $I
    ) {
        $I->wantToTest(
            'Http\Message\ServerRequest - getUploadedFiles() - exception'
        );
        $I->expectThrowable(
            new InvalidArgumentException('Invalid uploaded file'),
            function () use ($I) {
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

                $actual = $request->getUploadedFiles();
            }
        );
    }
}
