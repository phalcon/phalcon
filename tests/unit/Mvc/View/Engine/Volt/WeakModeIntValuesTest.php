<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Mvc\View\Engine\Volt;

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\View\Engine\Volt\Compiler;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string. cphalcon returns the value with no
 * change, thus the tests use assertEquals for the return values.
 *
 * @issue https://github.com/phalcon/phalcon/issues/885
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcViewEngineVoltCompilerFunctionAndFilterReturnInt(): void
    {
        $compiler = new Compiler();
        $compiler->addFunction(
            'answer',
            function () {
                return 42;
            }
        );
        $compiler->addFilter(
            'answer',
            function () {
                return 42;
            }
        );

        $this->assertSame('<?= 42 ?>', $compiler->compileString('{{ answer() }}'));
        $this->assertSame('<?= 42 ?>', $compiler->compileString('{{ x|answer }}'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcViewEngineVoltCompilerGetOptionBool(): void
    {
        $compiler = new Compiler();
        $compiler->setOption('autoescape', true);

        $this->assertEquals(true, $compiler->getOption('autoescape'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcViewEngineVoltConvertEncodingFailedDetection(): void
    {
        $volt = new Volt(new View());

        set_error_handler(
            function (): bool {
                return true;
            }
        );

        try {
            $actual = $volt->convertEncoding("\xff\xfe\xfd", 'ASCII,JIS', 'UTF-8');
        } finally {
            restore_error_handler();
        }

        $this->assertEquals(false, $actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testMvcViewEngineVoltHelpersInt(): void
    {
        $volt = new Volt(new View());

        $this->assertTrue($volt->isIncluded(5, '12345'));
        $this->assertSame(3, $volt->length(123));
        $this->assertSame('23', $volt->slice(12345, 1, 2));
        $this->assertEquals(123, $volt->preload(123));
    }
}
