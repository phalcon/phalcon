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

namespace Phalcon\Tests\Unit\Support\Helper\File;

use Codeception\Example;
use Phalcon\Support\Helper\File\Basename;
use UnitTester;

use function basename;

use const DIRECTORY_SEPARATOR;

class BasenameCest
{
    /**
     * Tests Phalcon\Support\Helper\File :: basename() with ASCII $uri
     * it should be same as PHP's basename
     *
     * @dataProvider getAsciiExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Ian Hu <hu2008yinxiang@163.com>
     * @since        2020-09-09
     */
    public function supportHelperFileBasenamePureASCII(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Helper\File - basename() with pure ASCII uri');

        $object = new Basename();
        $path   = $example[0];
        $suffix = $example[1];

        $expected = basename($path, $suffix);
        $actual   = $object($path, $suffix);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Helper\File :: basename() with non-ASCII $uri support
     *
     * @dataProvider getNonAsciiExamples
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author       Ian Hu <hu2008yinxiang@163.com>
     * @since        2020-09-09
     */
    public function supportHelperFileBasenameNonASCII(UnitTester $I, Example $example)
    {
        $I->wantToTest('Support\Fs - basename() with non-ASCII uri');

        $object   = new Basename();
        $path     = $example[0];
        $expected = $example[1];
        $actual   = $object($path);
        $I->assertEquals($expected, $actual);
    }

    /**
     * @return string[][]
     */
    private function getAsciiExamples(): array
    {
        return [
            [
                DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'sudoers.d',
                '.d',
            ],
            [
                DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'sudoers.d',
                '',
            ],
            [
                DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . 'passwd',
                '',
            ],
            [
                DIRECTORY_SEPARATOR . 'etc' . DIRECTORY_SEPARATOR . '',
                '',
            ],
            [
                '.',
                '',
            ],
            [
                DIRECTORY_SEPARATOR . '',
                '',
            ],
        ];
    }

    private function getNonAsciiExamples(): array
    {
        return [
            [
                DIRECTORY_SEPARATOR . 'file' . DIRECTORY_SEPARATOR . '热爱中文.txt',
                '热爱中文.txt',
            ],
            [
                DIRECTORY_SEPARATOR . '中文目录' . DIRECTORY_SEPARATOR . '热爱中文.txt',
                '热爱中文.txt',
            ],
            [
                DIRECTORY_SEPARATOR . 'myfolder' . DIRECTORY_SEPARATOR . '日本語のファイル名.txt',
                '日本語のファイル名.txt',
            ],
            [
                DIRECTORY_SEPARATOR . 'のファ' . DIRECTORY_SEPARATOR . '日本語のファイル名.txt',
                '日本語のファイル名.txt',
            ],
            [
                DIRECTORY_SEPARATOR . 'root' . DIRECTORY_SEPARATOR . 'ελληνικά.txt',
                'ελληνικά.txt',
            ],
            [
                DIRECTORY_SEPARATOR . 'νικά' . DIRECTORY_SEPARATOR . 'ελληνικά.txt',
                'ελληνικά.txt',
            ],
        ];
    }
}
