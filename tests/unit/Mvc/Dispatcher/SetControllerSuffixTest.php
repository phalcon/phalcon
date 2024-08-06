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

class SetControllerSuffixTest extends BaseDispatcher
{
    /**
     * Tests Phalcon\Mvc\Dispatcher :: setControllerSuffix()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-06-01
     */
    public function testMvcDispatcherSetControllerSuffix(): void
    {
        $dispatcher = $this->getDispatcher();

        $dispatcher->setControllerSuffix('Bleh');

        $this->assertEquals(
            'Bleh',
            $dispatcher->getHandlerSuffix()
        );
    }
}
