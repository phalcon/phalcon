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
use Phalcon\Events\Exception as EventsException;
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
     * Tests Phalcon\Autoload\Loader :: setFileCheckingCallback() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     *
     * @return void
     * @throws Exception
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
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13360
     * @issue  https://github.com/phalcon/cphalcon/issues/10472
     *
     * @return void
     * @throws EventsException
     * @throws Exception
     */
    public function testAutoloaderLoaderSetFileCheckingCallbackFalse(): void
    {
        $loader = new Loader();

        $loader
            ->setFiles(
                [
                    dataDir('fixtures/Loader/Example/Functions/FunctionsNoClassThree.php'),
                ]
            )
            ->setNamespaces(
                [
                    'Example' => dataDir('fixtures/Loader/Example/'),
                ],
                true
            )
            ->setFileCheckingCallback(
                function ($file) {
                    return false;
                }
            )
            ->register()
        ;

        $actual = function_exists('noClass3Foo');
        $this->assertFalse($actual);

        $actual = function_exists('noClass3Bar');
        $this->assertFalse($actual);

        $loader->unregister();
    }

    /**
     * Tests Phalcon\Autoload\Loader :: setFileCheckingCallback()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13360
     * @issue  https://github.com/phalcon/cphalcon/issues/10472
     *
     * @param string|null $callback
     *
     * @return void
     * @throws Exception
     * @throws EventsException
     */
    public function testAutoloaderLoaderSetFileCheckingCallbackValid(
        ?string $callback
    ): void {
        $loader = new Loader();

        $loader
            ->setFiles(
                [
                    dataDir('fixtures/Loader/Example/Functions/FunctionsNoClassThree.php'),
                ]
            )
            ->setNamespaces(
                [
                    'Example\Namespaces' => dataDir('fixtures/Loader/Example/Namespaces'),
                ],
                true
            )
            ->setFileCheckingCallback($callback)
            ->register()
        ;

        $actual = function_exists('noClass3Foo');
        $this->assertTrue($actual);

        $actual = function_exists('noClass3Bar');
        $this->assertTrue($actual);

        $actual = class_exists('\Example\Namespaces\Engines\Diesel');
        $this->assertTrue($actual);

        $loader->unregister();
    }
}
