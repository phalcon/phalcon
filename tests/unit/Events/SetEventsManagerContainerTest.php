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

namespace Phalcon\Tests\Unit\Events;

use Closure;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php;
use Phalcon\Mvc\View\Simple;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use Phalcon\Tests\Support\Controllers\ViewRequestController;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * These components set only their own events manager, as in cphalcon. The
 * "eventsManager" service of the container does not change.
 */
final class SetEventsManagerContainerTest extends AbstractUnitTestCase
{
    /**
     * @return array<string, array{0: Closure(DiInterface): EventsAwareInterface}>
     */
    public static function getExamples(): array
    {
        return [
            'Mvc\Controller'               => [
                static function (DiInterface $container): EventsAwareInterface {
                    $component = new ViewRequestController();
                    $component->setDI($container);

                    return $component;
                },
            ],
            'Mvc\Micro'                    => [
                static fn (DiInterface $container): EventsAwareInterface => new Micro($container),
            ],
            'Mvc\Model\Manager'            => [
                static function (DiInterface $container): EventsAwareInterface {
                    $component = new ModelsManager();
                    $component->setDI($container);

                    return $component;
                },
            ],
            'Mvc\Router'                   => [
                static function (DiInterface $container): EventsAwareInterface {
                    $component = new Router(false);
                    $component->setDI($container);

                    return $component;
                },
            ],
            'Mvc\View'                     => [
                static function (DiInterface $container): EventsAwareInterface {
                    $component = new View();
                    $component->setDI($container);

                    return $component;
                },
            ],
            'Mvc\View\Engine\Php'          => [
                static fn (DiInterface $container): EventsAwareInterface => new Php(new View(), $container),
            ],
            'Mvc\View\Simple'              => [
                static function (DiInterface $container): EventsAwareInterface {
                    $component = new Simple();
                    $component->setDI($container);

                    return $component;
                },
            ],
        ];
    }

    /**
     * @issue  https://github.com/phalcon/phalcon/issues/835
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-18
     */
    #[DataProvider('getExamples')]
    public function testEventsSetEventsManagerKeepsTheContainerService(
        Closure $factory
    ): void {
        $container     = new FactoryDefault();
        $eventsManager = new Manager();
        $component     = $factory($container);

        $component->setEventsManager($eventsManager);

        $this->assertSame($eventsManager, $component->getEventsManager());
        $this->assertNotSame($eventsManager, $container->get('eventsManager'));
    }
}
