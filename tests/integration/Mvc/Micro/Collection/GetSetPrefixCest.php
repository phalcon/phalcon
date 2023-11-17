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

namespace Phalcon\Tests\Integration\Mvc\Micro\Collection;

use IntegrationTester;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection;
use Phalcon\Mvc\Router\Route;
use Phalcon\Tests\Controllers\Micro\Collections\CustomersController;

class GetSetPrefixCest
{
    /**
     * Tests Phalcon\Mvc\Micro\Collection :: getPrefix()/setPrefix()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function mvcMicroCollectionGetSetPrefix(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\Micro\Collection - getPrefix()/setPrefix()');

        $app        = new Micro();
        $collection = new Collection();
        $controller = new CustomersController();
        $url        = '/customers';

        $actual   = $collection->getPrefix();
        $I->assertEmpty($actual);

        $collection->setPrefix($url);

        $expected = $url;
        $actual   = $collection->getPrefix();
        $I->assertSame($expected, $actual);

        $collection->setHandler($controller);

        $collection->map('/', 'index');
        $collection->map('/edit/{number}', 'edit');

        $app->mount($collection);
        $app->handle($url);

        $expected = 1;
        $actual   = $controller->getEntered();
        $I->assertSame($expected, $actual);


        $app->handle('/customers/edit/100');

        $expected = 101;
        $actual   = $controller->getEntered();
        $I->assertSame($expected, $actual);
    }
}
