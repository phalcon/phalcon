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

namespace Phalcon\Tests\Unit\Acl\Adapter\Memory;

use Phalcon\Acl\Adapter\Memory;
use Phalcon\Events\Manager;
use Phalcon\Tests\UnitTestCase;

final class GetSetEventsManagerTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Acl\Adapter\Memory :: getEventsManager()/setEventsManager()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testAclAdapterMemoryGetSetEventsManager(): void
    {
        $acl     = new Memory();
        $manager = new Manager();

        $acl->setEventsManager($manager);

        $class  = Manager::class;
        $actual = $acl->getEventsManager();
        $this->assertInstanceOf($class, $actual);
        $this->assertSame($manager, $actual);
    }
}