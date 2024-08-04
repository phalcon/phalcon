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
use Phalcon\Support\Helper\Str\Decrement;
use Phalcon\Tests\UnitTestCase;

final class DecrementTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Helper\Str :: decrement() - string
     *
     * @dataProvider getExamples
     *
     * @param Example $example
     *
     * @return void
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testSupportHelperStrDecrement(
        string $source,
        string $expected,
        string $separator
    ): void {
        $object = new Decrement();
        $actual = $object($source, $separator);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public static function getExamples()
    {
        return [
            ['file_2', 'file_1', '_'],
            ['file_1', 'file', '_'],
            ['file_3', 'file_2', '_'],
            ['file _1', 'file ', '_'],
            ['file-1', 'file', '-'],
        ];
    }
}
