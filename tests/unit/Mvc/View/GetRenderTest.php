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

use Phalcon\Di\Di;
use Phalcon\Mvc\View;
use Phalcon\Tests\UnitTestCase;

use function dataDir;

class GetRenderTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\View :: getRender()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcViewGetRender(): void
    {
        $container = new Di();
        $view = new View();

        $view->setViewsDir(
            $this->getDirSeparator(dataDir('fixtures/views'))
        );

        $view->setDI($container);

        $actual = $view->getRender(
            'partials',
            'partial',
            [
                'cool_var' => 'abcde',
            ]
        );

        $this->assertEquals(
            'Hey, this is a partial, also abcde',
            $actual
        );
    }
}
