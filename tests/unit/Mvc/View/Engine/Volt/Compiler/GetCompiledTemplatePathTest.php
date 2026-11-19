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

namespace Phalcon\Tests\Unit\Mvc\View\Engine\Volt\Compiler;

use Phalcon\Mvc\View\Engine\Volt\Compiler;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Talon\Talon;
use TypeError;

class GetCompiledTemplatePathTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcViewEngineVoltCompilerGetCompiledTemplatePath(): void
    {
        $viewFile    = Talon::settings()->supportPath('assets/views/compiler/partial.volt');
        $compileFile = $viewFile . '.php';

        $compiler = new Compiler();
        $compiler->compile($viewFile);

        $this->assertSame($compileFile, $compiler->getCompiledTemplatePath());

        $this->safeDeleteFile($compileFile);
    }

    /**
     * Before compile() there is no compiled path. The string return type
     * gives a TypeError, as in cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/838
     */
    public function testMvcViewEngineVoltCompilerGetCompiledTemplatePathBeforeCompile(): void
    {
        $compiler = new Compiler();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Phalcon\Mvc\View\Engine\Volt\Compiler::getCompiledTemplatePath(): '
            . 'Return value must be of type string, null returned'
        );

        $compiler->getCompiledTemplatePath();
    }
}
