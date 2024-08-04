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

namespace Phalcon\Tests\Unit\Support\Debug\Dump;

use Phalcon\Support\Debug\Dump;
use Phalcon\Tests\UnitTestCase;

final class ToJsonTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Debug\Dump :: toJson()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportDebugDumpToJson(): void
    {
        $test = [
            'key' => 'value',
        ];
        $dump = new Dump();

        $expected = "{\n    \"key\": \"value\"\n}";
        $actual   = $dump->toJson($test);
        $this->assertSame($expected, $actual);
    }
}
