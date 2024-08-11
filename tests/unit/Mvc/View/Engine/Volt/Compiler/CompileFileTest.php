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

namespace Phalcon\Tests\Unit\Mvc\View\Engine\Volt\Compiler;

use Phalcon\Mvc\View\Engine\Volt\Compiler;
use Phalcon\Tests\AbstractUnitTestCase;

class CompileFileTest extends AbstractUnitTestCase
{
    public static function defaultFilterProvider(): array
    {
        return [
            [
                'default',
                "<?= (empty(\$robot->price) ? (10.0) : (\$robot->price)) ?>\n",
            ],

            [
                'default_json_encode',
                "<?= json_encode((empty(\$preparedParams) ? ([]) : (\$preparedParams))) ?>\n",
            ],
        ];
    }

    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: compileFile()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2017-01-17
     */
    public function testMvcViewEngineVoltCompilerCompileFile(): void
    {
        $viewFile    = dataDir('fixtures/views/layouts/compiler.volt');
        $compileFile = $viewFile . '.php';

        $expected = '<?php if ($some_eval) { ?>
Clearly, the song is: <?= $this->getContent() ?>.
<?php } ?>';

        $volt = new Compiler();

        $volt->compileFile($viewFile, $compileFile);
        $this->assertFileContentsEqual($compileFile, $expected);

        $this->safeDeleteFile($compileFile);
    }

    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: compileFile()
     *
     * @issue https://github.com/phalcon/cphalcon/issues/13242
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     *
     * @dataProvider defaultFilterProvider
     */
    public function testMvcViewEngineVoltCompilerCompileFileDefaultFilter(
        string $view,
        string $expected
    ): void {
        $volt = new Compiler();

        $viewFile = sprintf(
            '%sfixtures/views/filters/%s.volt',
            dataDir(),
            $view
        );

        $compiledFile = $viewFile . '.php';

        $volt->compileFile($viewFile, $compiledFile);

        $this->assertFileContentsEqual($compiledFile, $expected);

        $this->safeDeleteFile($compiledFile);
    }
}
