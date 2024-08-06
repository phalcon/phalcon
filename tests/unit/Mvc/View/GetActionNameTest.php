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
use Phalcon\Tests\UnitTestCase;

class GetActionNameTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Mvc\View :: getActionName()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcViewGetActionName(): void
    {
        $this->setNewFactoryDefault();
        $this->setDiService('view');
        $view = $this->getService('view');

        $view->start();

        $view->render('simple', 'index');

        $view->finish();

        $this->assertEquals(
            'index',
            $view->getActionName()
        );
    }
}
