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
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Autoload\Fake\LoaderTrait;

use function class_exists;
use function function_exists;

final class SetFileCheckingCallbackTest extends AbstractUnitTestCase
{
    use LoaderTrait;

    /**
     * @return array
     */
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
     * Tests Phalcon\Autoload\Loader :: setFileCheckingCallback() - callback false
     *
     * @return void
     *
     * @throws EventsException
     * @throws Exception
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13360
     * @issue  https://github.com/phalcon/cphalcon/issues/10472
     */
    public function testAutoloaderLoaderSetFileCheckingCallbackFalse(): void
    {
        $loader = new Loader();

        $loader
            ->setFiles(
                [
                    supportDir('assets/Loader/Example/Functions/FunctionsNoClassThree.php'),
                ]
            )
            ->setNamespaces(
                [
                    'Example' => supportDir('assets/Loader/Example/'),
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
     * @param string|null $callback
     *
     * @return void
     *
     * @throws Exception
     * @throws EventsException
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * @issue  https://github.com/phalcon/cphalcon/issues/13360
     * @issue  https://github.com/phalcon/cphalcon/issues/10472
     */
    public function testAutoloaderLoaderSetFileCheckingCallbackValid(
        ?string $callback
    ): void {
        $loader = new Loader();

        $loader
            ->setFiles(
                [
                    supportDir('assets/Loader/Example/Functions/FunctionsNoClassThree.php'),
                ]
            )
            ->setNamespaces(
                [
                    'Example\Namespaces' => supportDir('assets/Loader/Example/Namespaces'),
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
