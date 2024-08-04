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

namespace Phalcon\Tests\Unit\Support\Helper\Str;

use Codeception\Example;
use Phalcon\Support\Helper\Str\Increment;
use Phalcon\Tests\UnitTestCase;

final class IncrementTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Str :: increment()
     *
     * @dataProvider strProvider
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrIncrementSimpleString(
        string $source,
        string $expected,
        string $separator
    ): void {
        $object   = new Increment();
        $actual   = $object($source, $separator);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public static function strProvider()
    {
        return [
            ["file", "file_1", "_"],
            ["file_1", "file_2", "_"],
            ["file_2", "file_3", "_"],
            ["file_", "file_1", "_"],
            ["file ","file _1", "_"],
            ["file", "file-1", "-"],
        ];
    }
}
