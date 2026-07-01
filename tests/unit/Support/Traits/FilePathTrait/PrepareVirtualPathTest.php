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

namespace Phalcon\Tests\Unit\Support\Traits\FilePathTrait;

use Phalcon\Support\Traits\FilePathTrait;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PrepareVirtualPathTest extends AbstractUnitTestCase
{
    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function getExamples(): array
    {
        return [
            'forward slashes'  => ['/some/path/to/file', '_', '_some_path_to_file'],
            'backslashes'      => ['some\\path\\to\\file', '_', 'some_path_to_file'],
            'colons'           => ['C:/Windows/System32', '_', 'C__Windows_System32'],
            'mixed'            => ['C:\\some/path:file', '_', 'C__some_path_file'],
            'custom separator' => ['/some/path', '-', '-some-path'],
            'no special chars' => ['simplepath', '_', 'simplepath'],
        ];
    }

    /**
     * Tests Phalcon\Support\Traits\FilePathTrait :: prepareVirtualPath()
     *
     * @return void
     *
     * @dataProvider getExamples
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-11
     */
    #[DataProvider('getExamples')]
    public function testSupportTraitsFilePathTraitPrepareVirtualPath(
        string $key,
        string $separator,
        string $expected
    ): void {
        $object = new class {
            use FilePathTrait;
        };

        $actual = $object->prepareVirtualPath($key, $separator);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Traits\FilePathTrait :: prepareVirtualPath() - default separator
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-11
     */
    public function testSupportTraitsFilePathTraitPrepareVirtualPathDefaultSeparator(): void
    {
        $object = new class {
            use FilePathTrait;
        };

        $actual   = $object->prepareVirtualPath('/some/path');
        $expected = '_some_path';

        $this->assertSame($expected, $actual);
    }
}
