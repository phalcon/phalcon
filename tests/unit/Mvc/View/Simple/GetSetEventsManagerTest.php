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

namespace Phalcon\Tests\Unit\Mvc\View\Simple;

use Phalcon\Mvc\View\Simple;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\UnitTestCase;

class GetSetEventsManagerTest extends UnitTestCase
{
    use DiTrait;

    /**
     * Tests Phalcon\Mvc\View\Simple :: getEventsManager()/setEventsManager()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcViewSimpleGetSetEventsManager(): void
    {
        $manager = $this->newService('eventsManager');
        $view    = new Simple();

        $view->setEventsManager($manager);

        $actual = $view->getEventsManager();
        $this->assertEquals($manager, $actual);
    }
}
