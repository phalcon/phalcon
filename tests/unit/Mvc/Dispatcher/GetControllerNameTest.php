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

use Phalcon\Tests\Unit\Mvc\Dispatcher\Helper\BaseDispatcher;

class GetControllerNameTest extends BaseDispatcher
{
    /**
     * Tests Phalcon\Mvc\Dispatcher :: getControllerName()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcDispatcherGetControllerName(): void
    {
        $dispatcher = $this->getDispatcher();
        $this->assertSame('dispatcher-test-default', $dispatcher->getControllerName());
    }

    /**
     * Tests Phalcon\Mvc\Dispatcher :: getControllerName() - PascalCase single word
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-21
     * @issue  https://github.com/phalcon/cphalcon/issues/15996
     */
    public function testMvcDispatcherGetControllerNameWithPascalCase(): void
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->setControllerName('Page');
        $this->assertSame('page', $dispatcher->getControllerName());
    }

    /**
     * Tests Phalcon\Mvc\Dispatcher :: getControllerName() - PascalCase multi-word
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-21
     * @issue  https://github.com/phalcon/cphalcon/issues/15996
     */
    public function testMvcDispatcherGetControllerNameWithMultiWordPascalCase(): void
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->setControllerName('DispatcherTestDefault');
        $this->assertSame('dispatcher_test_default', $dispatcher->getControllerName());
    }
}
