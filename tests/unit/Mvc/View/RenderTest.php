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

namespace Phalcon\Tests\Unit\Mvc\View;

use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\ViewTrait;
use Phalcon\Tests\AbstractUnitTestCase;

class RenderTest extends AbstractUnitTestCase
{
    use DiTrait;
    use ViewTrait;

    public function testMvcViewRenderMultiple(): void
    {
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
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests View::render with params
     *
     * @author Serghei Iakovlev <serghei@phalcon.io>
     * @since  2017-09-24
     * @issue  https://github.com/phalcon/cphalcon/issues/13046
     */
    public function testMvcViewRenderWithParams(): void
    {
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
        $this->assertEquals($expected, $actual);
    }
}
