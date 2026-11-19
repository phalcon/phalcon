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
use Phalcon\Mvc\View\Engine\Volt\Exceptions\InvalidOptionType;
use Phalcon\Mvc\View\Engine\Volt\Exceptions\InvalidPathType;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Talon\Talon;
use Phalcon\Tests\Support\Traits\ErrorHandlerTrait;
use PHPUnit\Framework\Attributes\DataProvider;

use function file_get_contents;
use function file_put_contents;
use function mkdir;

/**
 * The compile options of cphalcon: a deprecated option name is used with a
 * deprecation notice, and a `null` value is a value.
 *
 * @issue https://github.com/phalcon/phalcon/issues/836
 */
final class CompileOptionsTest extends AbstractUnitTestCase
{
    use ErrorHandlerTrait;

    private string $directory;

    private string $template;

    public function setUp(): void
    {
        $this->directory = Talon::settings()->outputPath('tests/volt-compile-options/');
        $this->template  = $this->directory . 'index.volt';

        $this->safeDeleteDirectory($this->directory);
        mkdir($this->directory . 'compiled/', 0777, true);
        file_put_contents($this->template, 'Hello');
    }

    public function tearDown(): void
    {
        $this->safeDeleteDirectory($this->directory);
    }

    /**
     * @return array<string, array{0: string, 1: class-string, 2: string}>
     */
    public static function nullOptionProvider(): array
    {
        return [
            'always'    => ['always', InvalidOptionType::class, "'always' must be a bool value"],
            'extension' => ['extension', InvalidOptionType::class, "'extension' must be a string"],
            'path'      => ['path', InvalidPathType::class, "'path' must be a string or a closure"],
            'prefix'    => ['prefix', InvalidOptionType::class, "'prefix' must be a string"],
            'separator' => ['separator', InvalidOptionType::class, "'separator' must be a string"],
        ];
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testMvcViewEngineVoltCompilerCompileAlwaysDeprecatedName(): void
    {
        $compiler = new Compiler();
        $compiler->setOption('compileAlways', true);

        [, $messages] = $this->collectErrors(
            function () use ($compiler): void {
                $compiler->compile($this->template);
                file_put_contents($this->template, 'Bye');
                $compiler->compile($this->template);
            }
        );

        $expected = "The 'compileAlways' option is deprecated. Use 'always' instead.";
        $this->assertSame([$expected, $expected], $messages);
        $this->assertSame(
            'Bye',
            file_get_contents($compiler->getCompiledTemplatePath())
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testMvcViewEngineVoltCompilerCompiledExtensionDeprecatedName(): void
    {
        $compiler = new Compiler();
        $compiler->setOption('compiledExtension', '.compiled');

        [, $messages] = $this->collectErrors(
            fn () => $compiler->compile($this->template)
        );

        $this->assertSame(
            ["The 'compiledExtension' option is deprecated. Use 'extension' instead."],
            $messages
        );
        $this->assertSame(
            $this->template . '.compiled',
            $compiler->getCompiledTemplatePath()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testMvcViewEngineVoltCompilerCompiledPathDeprecatedName(): void
    {
        $compiled = $this->directory . 'compiled/';
        $compiler = new Compiler();
        $compiler->setOption('compiledPath', $compiled);

        [, $messages] = $this->collectErrors(
            fn () => $compiler->compile($this->template)
        );

        $this->assertSame(
            ["The 'compiledPath' option is deprecated. Use 'path' instead."],
            $messages
        );
        $this->assertStringStartsWith(
            $compiled,
            $compiler->getCompiledTemplatePath()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    public function testMvcViewEngineVoltCompilerCompiledSeparatorDeprecatedName(): void
    {
        $compiler = new Compiler();
        $compiler->setOption('path', $this->directory . 'compiled/');
        $compiler->setOption('compiledSeparator', '_');

        [, $messages] = $this->collectErrors(
            fn () => $compiler->compile($this->template)
        );

        $this->assertSame(
            ["The 'compiledSeparator' option is deprecated. Use 'separator' instead."],
            $messages
        );
        $this->assertStringEndsWith(
            '_index.volt.php',
            $compiler->getCompiledTemplatePath()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    #[DataProvider('nullOptionProvider')]
    public function testMvcViewEngineVoltCompilerCompileNullOption(
        string $option,
        string $exception,
        string $message
    ): void {
        $compiler = new Compiler();
        $compiler->setOption($option, null);

        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        $compiler->compile($this->template);
    }
}
