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

namespace Phalcon\Tests\Unit\Di\FactoryDefault\Cli;

use Codeception\Example;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Escaper\Escaper;
use Phalcon\Events\Manager as EventsManager;
use UnitTester;

/**
 * Class ConstructCest
 *
 * @package Phalcon\Tests\Unit\Di\FactoryDefault\Cli
 */
class ConstructCest
{
    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: __construct()
     *
     * @param  UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diFactoryDefaultCliConstruct(UnitTester $I)
    {
        $I->wantToTest('Di\FactoryDefault\Cli - __construct()');

        $container = new Cli();
        $services  = $this->getServices();

        $expected = count($services);
        $actual   = count($container->getServices());
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: __construct() - Check services
     *
     * @dataProvider getServices
     *
     * @param  UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function diFactoryDefaultCliConstructServices(UnitTester $I, Example $example)
    {
        $I->wantToTest('Di\FactoryDefault\Cli - __construct() - Check services');

        $container = new Cli();

        $class  = $example['class'];
        $actual = $container->get($example['service']);
        $I->assertInstanceOf($class, $actual);
    }

    /**
     * @return string[][]
     */
    private function getServices(): array
    {
        return [
            [
                'service' => 'escaper',
                'class'   => Escaper::class,
            ],
            [
                'service' => 'eventsManager',
                'class'   => EventsManager::class,
            ],
        ];
    }
}
