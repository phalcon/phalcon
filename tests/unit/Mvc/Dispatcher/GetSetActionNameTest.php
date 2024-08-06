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

namespace Phalcon\Tests\Unit\Mvc\Dispatcher;

use Phalcon\Mvc\Dispatcher;
use Phalcon\Tests\UnitTestCase;

class GetSetActionNameTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Dispatcher :: getActionName() / setActionName()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcDispatcherGetActionName(): void
    {
        $dispatcher = new Dispatcher();

        $dispatcher->setActionName('login');

        $this->assertEquals(
            'login',
            $dispatcher->getActionName()
        );
    }
}
