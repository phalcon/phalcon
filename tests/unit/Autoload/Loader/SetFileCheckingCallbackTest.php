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

namespace Phalcon\Tests\Unit\Autoload\Loader;

use Phalcon\Autoload\Exception;
use Phalcon\Autoload\Loader;
use Phalcon\Tests\Fixtures\Traits\LoaderTrait;
use Phalcon\Tests\UnitTestCase;

use function class_exists;
use function function_exists;

final class SetFileCheckingCallbackTest extends UnitTestCase
{
    use LoaderTrait;

    public static function getExamples(): array
    {
        return [
            [
                'stream_resolve_include_path',
            ],
            [
                null,
            ],
        ];
    }

    /**
     * Tests Phalcon\Autoload\Loader :: setFileCheckingCallback()
     *
     * @dataProvider getExamples
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13360
     * @issue  https://github.com/phalcon/cphalcon/issues/10472
     */
    public function testAutoloaderLoaderSetFileCheckingCallback(
        ?string $callback
    ): void {
        $loader = new Loader();

        $loader->setFileCheckingCallback($callback);

        $loader->setFiles(
            [
                dataDir('fixtures/Loader/Example/Functions/FunctionsNoClassThree.php'),
            ]
        );

        $loader->setNamespaces(
            [
                'Example\Namespaces' => dataDir('fixtures/Loader/Example/Namespaces'),
            ],
            true
        );

        $loader->register();

        $actual = function_exists('noClass3Foo');
        $this->assertTrue($actual);

        $actual = function_exists('noClass3Bar');
        $this->assertTrue($actual);

        $actual = class_exists('\Example\Namespaces\Engines\Diesel');
        $this->assertTrue($actual);
    }

    /**
     * Tests Phalcon\Autoload\Loader :: setFileCheckingCallback() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAutoloaderLoaderSetFileCheckingCallbackException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "The 'method' parameter must be either a callable or NULL"
        );

        $loader = new Loader();
        $loader->setFileCheckingCallback(1234);
    }

    /**
     * Tests Phalcon\Autoload\Loader :: setFileCheckingCallback()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13360
     * @issue  https://github.com/phalcon/cphalcon/issues/10472
     */
    public function testAutoloaderLoaderSetFileCheckingCallbackFalse(): void
    {
        $loader = new Loader();

        $loader->setFileCheckingCallback(
            function ($file) {
                return false;
            }
        );

        $loader->setFiles(
            [
                dataDir('fixtures/Loader/Example/Functions/FunctionsNoClassThree.php'),
            ]
        );

        $loader->setNamespaces(
            [
                'Example' => dataDir('fixtures/Loader/Example/'),
            ],
            true
        );

        $loader->register();

        $actual = function_exists('noClass3Foo');
        $this->assertFalse($actual);

        $actual = function_exists('noClass3Bar');
        $this->assertFalse($actual);
    }
}
