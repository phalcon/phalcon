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

namespace Phalcon\Tests\Unit\Di\FactoryDefault;

use Codeception\Example;
use Phalcon\Assets\Manager as ManagerAssets;
use Phalcon\Di\FactoryDefault;
use Phalcon\Encryption\Crypt;
use Phalcon\Encryption\Security;
use Phalcon\Events\Manager as ManagerEvents;
use Phalcon\Filter\Filter;
use Phalcon\Flash\Direct;
use Phalcon\Flash\Session;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;
use Phalcon\Http\Response;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\MetaData\Memory;
use Phalcon\Mvc\Model\Transaction\Manager;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\CliTrait;
use UnitTester;

class ConstructCest
{
    use CliTrait;

    /**
     * Tests Phalcon\Di\FactoryDefault :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function diFactoryDefaultConstruct(UnitTester $I)
    {
        $I->wantToTest('Di\FactoryDefault - __construct()');

        $container = new FactoryDefault();

        $expected = 10;
        $actual   = count($container->getServices());
        $I->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Di\FactoryDefault :: __construct() - Check services
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     *
     * @dataProvider getServices
     */
    public function diFactoryDefaultConstructServices(UnitTester $I, Example $example)
    {
        $I->wantToTest('Di\FactoryDefault - __construct() - ' . $example['service']);

        $container = new FactoryDefault();

        if ('sessionBag' === $example['service']) {
            $params = ['someName'];
        } else {
            $params = null;
        }

        $class  = $example['class'];
        $actual = $container->get($example['service'], $params);
        $I->assertInstanceOf($class, $actual);
    }
}
