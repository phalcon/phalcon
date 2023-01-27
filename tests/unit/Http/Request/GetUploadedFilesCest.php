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

namespace Phalcon\Tests\Unit\Http\Request;

use Page\Http;
use Phalcon\Http\Request;
use Phalcon\Tests\Unit\Http\Helper\HttpBase;
use UnitTester;

class GetUploadedFilesCest extends HttpBase
{
    /**
     * Tests Phalcon\Http\Request :: getUploadedFiles()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-03-17
     */
    public function httpRequestGetUploadedFiles(UnitTester $I)
    {
        $I->wantToTest('Http\Request - getUploadedFiles()');

        $_FILES['photo'] = [
            'name'     => ['f0', 'f1', ['f2', 'f3'], [[[['f4']]]]],
            'type'     => [
                Http::CONTENT_TYPE_PLAIN,
                Http::CONTENT_TYPE_CSV,
                ['image/png', 'image/gif'],
                [[[['application/octet-stream']]]],
            ],
            'tmp_name' => ['t0', 't1', ['t2', 't3'], [[[['t4']]]]],
            'error'    => [0, 0, [0, 0], [[[[8]]]]],
            'size'     => [10, 20, [30, 40], [[[[50]]]]],
        ];

        $request    = $this->getRequestObject();
        $all        = $request->getUploadedFiles();
        $successful = $request->getUploadedFiles(true);

        $expected = 5;
        $actual   = $all;
        $I->assertCount($expected, $actual);

        $expected = 4;
        $actual   = $successful;
        $I->assertCount($expected, $actual);

        for ($counter = 0; $counter <= 4; ++$counter) {
            $actual = $all[$counter]->isUploadedFile();
            $I->assertFalse($actual);
        }

        $data = [
            'photo.0',
            'photo.1',
            'photo.2.0',
            'photo.2.1',
            'photo.3.0.0.0.0',
        ];

        for ($counter = 0; $counter <= 4; ++$counter) {
            $expected = $data[$counter];
            $actual   = $all[$counter]->getKey();

            $I->assertSame($expected, $actual);
        }

        $expected = 'f0';
        $actual   = $all[0]->getName();
        $I->assertSame($expected, $actual);
        $expected = 'f1';
        $actual   = $all[1]->getName();
        $I->assertSame($expected, $actual);
        $expected = 'f2';
        $actual   = $all[2]->getName();
        $I->assertSame($expected, $actual);
        $expected = 'f3';
        $actual   = $all[3]->getName();
        $I->assertSame($expected, $actual);
        $expected = 'f4';
        $actual   = $all[4]->getName();
        $I->assertSame($expected, $actual);

        $expected = 't0';
        $actual   = $all[0]->getTempName();
        $I->assertSame($expected, $actual);
        $expected = 't1';
        $actual   = $all[1]->getTempName();
        $I->assertSame($expected, $actual);
        $expected = 't2';
        $actual   = $all[2]->getTempName();
        $I->assertSame($expected, $actual);
        $expected = 't3';
        $actual   = $all[3]->getTempName();
        $I->assertSame($expected, $actual);
        $expected = 't4';
        $actual   = $all[4]->getTempName();
        $I->assertSame($expected, $actual);

        $expected = 'f0';
        $actual   = $successful[0]->getName();
        $I->assertSame($expected, $actual);
        $expected = 'f1';
        $actual   = $successful[1]->getName();
        $I->assertSame($expected, $actual);
        $expected = 'f2';
        $actual   = $successful[2]->getName();
        $I->assertSame($expected, $actual);
        $expected = 'f3';
        $actual   = $successful[3]->getName();
        $I->assertSame($expected, $actual);

        $expected = 't0';
        $actual   = $successful[0]->getTempName();
        $I->assertSame($expected, $actual);
        $expected = 't1';
        $actual   = $successful[1]->getTempName();
        $I->assertSame($expected, $actual);
        $expected = 't2';
        $actual   = $successful[2]->getTempName();
        $I->assertSame($expected, $actual);
        $expected = 't3';
        $actual   = $successful[3]->getTempName();
        $I->assertSame($expected, $actual);
    }
}
