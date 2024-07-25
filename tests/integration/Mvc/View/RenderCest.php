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

namespace Phalcon\Tests\Integration\Mvc\View;

use IntegrationTester;
use Phalcon\Mvc\View;
use Phalcon\Tests\Fixtures\Objects\ChildObject;
use Phalcon\Tests\Fixtures\Objects\ParentObject;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\ViewTrait;

class RenderCest
{
    use DiTrait;
    use ViewTrait;

    /**
     * Tests View::render with params
     *
     * @author Serghei Iakovlev <serghei@phalcon.io>
     * @since  2017-09-24
     * @issue  https://github.com/phalcon/cphalcon/issues/13046
     */
    public function mvcViewRenderWithParams(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\View - render() with parameters');

        $this->setNewFactoryDefault();
        $this->setDiService('view');
        $view = $this->getService('view');

        $view->start();
        $view->render(
            'simple',
            'params',
            [
                'name' => 'Sam',
                'age'  => 20,
            ]
        );
        $view->finish();

        $expected = 'My name is Sam and I am 20 years old';
        $actual   = $view->getContent();
        $I->assertEquals($expected, $actual);
    }

    public function mvcViewRenderMultiple(IntegrationTester $I)
    {
        $I->wantToTest('Mvc\View - render() multiple');

        $this->setNewFactoryDefault();
        $this->setDiService('view');
        $view = $this->getService('view');
        $view->setViewsDir(
            [
                dataDir('fixtures/views'),
                dataDir('fixtures/views-alt'),
            ]
        );

        $view->start();
        $view->render(
            'simple',
            'params',
            [
                'name' => 'Sam',
                'age'  => 20,
            ]
        );
        $view->finish();

        $expected = 'My name is Sam and I am 20 years old';
        $actual   = $view->getContent();
        $I->assertEquals($expected, $actual);
    }
}
