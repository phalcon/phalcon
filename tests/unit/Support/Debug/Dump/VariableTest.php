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

final class VariableTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Support\Debug\Dump :: variable() - name
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function debugDumpVariableName(): void
    {
        $test = 'value';
        $dump = new Dump();

        $expected = trim(
            file_get_contents(
                dataDir('fixtures/Support/Dump/variable_name_output.txt')
            )
        );

        $actual = $dump->variable($test, 'super');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Support\Debug\Dump :: variable()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportDebugDumpVariable(): void
    {
        $test = 'value';
        $dump = new Dump();

        $expected = trim(
            file_get_contents(
                dataDir('fixtures/Support/Dump/variable_output.txt')
            )
        );
        $actual   = $dump->variable($test);
        $this->assertSame($expected, $actual);
    }
}
