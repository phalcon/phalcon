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

use LoaderEvent;
use Phalcon\Autoload\Loader;
use Phalcon\Events\Manager;
use Phalcon\Tests\Fixtures\Traits\LoaderTrait;
use Phalcon\Tests\AbstractUnitTestCase;

use function array_pop;
use function spl_autoload_functions;

final class RegisterUnregisterTest extends AbstractUnitTestCase
{
    use LoaderTrait;

    /**
     * Tests Phalcon\Autoload\Loader :: events
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAutoloaderLoaderEvents(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        $trace   = [];
        $loader  = new Loader();
        $manager = new Manager();

        $loader
            ->setDirectories(
                [
                    dataDir('fixtures/Loader/Example/Events/'),
                ]
            )
            ->setClasses(
                [
                    'OtherClass' => dataDir('fixtures/Loader/Example/Events/Other/'),
                ]
            )
            ->setNamespaces(
                [
                    'Other\OtherClass' => dataDir('fixtures/Loader/Example/Events/Other/'),
                ]
            )
        ;

        $manager->attach(
            'loader',
            function ($event, $loader) use (&$trace) {
                $type = $event->getType();

                if (!isset($trace[$type])) {
                    $trace[$type] = [];
                }

                $trace[$type][] = $loader->getCheckedPath();
            }
        );

        $loader->setEventsManager($manager);

        $loader->register();

        $this->assertInstanceOf(LoaderEvent::class, new LoaderEvent());

        $expected = [
            'beforeCheckClass' => [
                0 => null,
            ],
            'beforeCheckPath'  => [
                0 => dataDir('fixtures/Loader/Example/Events/LoaderEvent.php'),
            ],
            'pathFound'        => [
                0 => dataDir('fixtures/Loader/Example/Events/LoaderEvent.php'),
            ],
        ];

        $this->assertSame($expected, $trace);

        $loader->unregister();
    }

    /**
     * Tests Phalcon\Autoload\Loader :: register()/unregister()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testAutoloaderLoaderRegisterUnregister(): void
    {
        $loader = new Loader();
        $loader->register();

        $functions = spl_autoload_functions();
        $item      = array_pop($functions);

        $this->assertSame($loader, $item[0]);
        $this->assertSame('autoload', $item[1]);

        $loader->unregister();
    }
}
