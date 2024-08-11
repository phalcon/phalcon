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

use Phalcon\Http\Message\UploadedFile;
use Phalcon\Tests\Fixtures\Page\Http;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetClientFilenameTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\UploadedFile :: getClientFilename()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-02-10
     */
    public function testHttpMessageUploadedFileGetClientFilename(): void
    {
        $file = new UploadedFile(
            Http::STREAM_MEMORY,
            0,
            UPLOAD_ERR_OK,
            'phalcon.txt'
        );

        $expected = 'phalcon.txt';
        $actual   = $file->getClientFilename();
        $this->assertSame($expected, $actual);
    }
}
