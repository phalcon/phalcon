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
use Phalcon\Tests\Fixtures\Support\Dump\ClassProperties;
use Phalcon\Tests\AbstractUnitTestCase;

use const PHP_OS_FAMILY;

final class ConstructTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Support\Debug\Dump :: __construct() - dump properties
     *
     * @issue  https://github.com/phalcon/cphalcon/issues/13315
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testSupportDebugDumpConstructDump(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $patient = new ClassProperties();
        $dump    = new Dump([], true);

        $actual = $this->callProtectedMethod($dump, 'output', $patient);

        $expected = file_get_contents(
            dataDir('fixtures/Support/Dump/class_properties.txt')
        );

        // Test without HTML
        $actual = strip_tags($actual);

        // Remove a trailing newline
        $expected = trim($expected);
        $this->assertSame($expected, $actual);
    }
}
