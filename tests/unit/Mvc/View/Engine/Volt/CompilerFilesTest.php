<?php

/*
 +------------------------------------------------------------------------+
 | Phalcon Framework                                                      |
 +------------------------------------------------------------------------+
 | Copyright (c) 2011-present Phalcon Team (https://phalcon.io)       |
 +------------------------------------------------------------------------+
 | This source file is subject to the New BSD License that is bundled     |
 | with this package in the file LICENSE.txt.                             |
 |                                                                        |
 | If you did not receive a copy of the license and are unable to         |
 | obtain it through the world-wide-web, please send an email             |
 | to license@phalcon.io so we can send you a copy immediately.       |
 +------------------------------------------------------------------------+
 */

namespace Phalcon\Tests\Unit\Mvc\View\Engine\Volt;

use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt\Compiler;
use Phalcon\Tests\AbstractUnitTestCase;

use function dataDir;
use function sprintf;

use const PHP_EOL;

class CompilerFilesTest extends AbstractUnitTestCase
{
    public function setUp(): void
    {
        $compiledFiles = [
            dataDir('fixtures/views/blocks/base.volt.php'),
            dataDir('fixtures/views/blocks/index/login.volt.php'),
            dataDir('fixtures/views/blocks/index/main.volt.php'),
            dataDir('fixtures/views/blocks/partials/header.volt.php'),
        ];
        foreach ($compiledFiles as $fileName) {
            $this->safeDeleteFile($fileName);
        }
    }

    public function tearDown(): void
    {
        $compiledFiles = [
            dataDir('fixtures/views/blocks/base.volt.php'),
            dataDir('fixtures/views/blocks/base.volt%%e%%.php'),
            dataDir('fixtures/views/blocks/index/login.volt.php'),
            dataDir('fixtures/views/blocks/index/main.volt.php'),
            dataDir('fixtures/views/blocks/partials/header.volt.php'),
            dataDir('fixtures/views/extends/children.extends.volt.php'),
            dataDir('fixtures/views/extends/import.volt.php'),
            dataDir('fixtures/views/extends/import2.volt.php'),
            dataDir('fixtures/views/layouts/extends.volt.php'),
            dataDir('fixtures/views/partials/header.volt.php'),
            dataDir('fixtures/views/partials/header2.volt.php'),
            dataDir('fixtures/views/partials/header3.volt.php'),
            dataDir('fixtures/views/partials/footer.volt.php'),
        ];

        foreach ($compiledFiles as $fileName) {
            $this->safeDeleteFile($fileName);
        }
    }

    /**
     * Tests Compiler::compileFile to compile files with blocks and partials
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-06-25
     */
    public function testMvcViewEngineVoltCompileBlocks(): void
    {
        $template = '<!DOCTYPE html>' . PHP_EOL
            . '<html lang="en">' . PHP_EOL
            . '<head>' . PHP_EOL
            . '    <meta charset="utf-8" />' . PHP_EOL
            . '    <meta name="viewport" content="width=device-width, initial-scale=1.0" />' . PHP_EOL
            . '</head>' . PHP_EOL
            . '<body>' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . '%s' . PHP_EOL
            . PHP_EOL
            . PHP_EOL
            . '</body>' . PHP_EOL
            . '</html>' . PHP_EOL;

        /**
         * Set up the view and Volt and compile
         */
        $view = new View();
        $view->setViewsDir(
            [
                dataDir('fixtures/views/blocks'),
            ]
        );

        $volt = new Compiler($view);

        /**
         * Login - no header output
         */
        $volt->compileFile(
            dataDir('fixtures/views/blocks/index/login.volt'),
            dataDir('fixtures/views/blocks/index/login.volt.php')
        );

        $file     = dataDir('fixtures/views/blocks/index/login.volt.php');
        $expected = sprintf($template, '<p>This is the login page</p>');
        $this->assertFileContentsEqual($file, $expected);

        /**
         * Main page = header output
         */
        $volt->compileFile(
            dataDir('fixtures/views/blocks/index/main.volt'),
            dataDir('fixtures/views/blocks/index/main.volt.php')
        );

        $file = dataDir('fixtures/views/blocks/index/main.volt.php');

        $expected = sprintf($template, '<p>This is the main page</p>');
        $this->assertFileContentsEqual($file, $expected);
    }

    /**
     * Tests Compiler::compileFile test case to compile extended files
     *
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-01-17
     */
    public function testMvcViewEngineVoltCompileExtendsFile(): void
    {
        $view = new View();
        $view->setViewsDir(dataDir('fixtures/views/'));

        $volt = new Compiler($view);

        //extends
        $volt->compileFile(
            dataDir('fixtures/views/extends/children.extends.volt'),
            dataDir('fixtures/views/extends/children.extends.volt.php')
        );

        $file     = dataDir('fixtures/views/extends/children.extends.volt.php');
        $contents = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">'
            . '<html lang="en"><html xmlns="http://www.w3.org/1999/xhtml">'
            . '<head><style type="text/css">.important { color: #336699; }</style>'
            . '<title>Index - My Webpage</title></head><body>'
            . '<div id="content"><h1>Index</h1><p class="important">Welcome on my awesome homepage.</p>'
            . '</div><div id="footer">&copy; Copyright 2012 by <a href="http://domain.invalid/">you</a>.'
            . '</div></body>';

        $this->assertFileContentsEqual($file, $contents);
    }

    /**
     * Tests Compiler::compileFile test case to compile imported files
     *
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-01-17
     */
    public function testMvcViewEngineVoltCompileImportFile(): void
    {
        $view = new View();
        $view->setViewsDir(dataDir('fixtures/views/'));

        $volt = new Compiler($view);

        //extends
        $volt->compileFile(
            dataDir('fixtures/views/extends/import.volt'),
            dataDir('fixtures/views/extends/import.volt.php')
        );

        $file     = dataDir('fixtures/views/extends/import.volt.php');
        $contents = '<div class="header"><h1>This is the header</h1></div>'
            . '<div class="footer"><p>This is the footer</p></div>';
        $this->assertFileContentsEqual($file, $contents);
    }

    /**
     * Tests Compiler::compileFile test case to compile imported files
     * recursively
     *
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-01-17
     */
    public function testMvcViewEngineVoltCompileImportRecursiveFiles(): void
    {
        $view = new View();
        $view->setViewsDir(dataDir('fixtures/views/'));

        $volt = new Compiler($view);

        //extends
        $volt->compileFile(
            dataDir('fixtures/views/extends/import2.volt'),
            dataDir('fixtures/views/extends/import2.volt.php')
        );

        $file     = dataDir('fixtures/views/extends/import2.volt.php');
        $contents = '<div class="header"><h1>This is the title</h1></div>';
        $this->assertFileContentsEqual($file, $contents);
    }
}
