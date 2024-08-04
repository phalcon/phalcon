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

namespace Phalcon\Tests\Unit\Application;

use Phalcon\Events\Manager;
use Phalcon\Tests\Fixtures\Application\ApplicationFixture;
use Phalcon\Tests\UnitTestCase;

use function spl_object_hash;

final class GetSetEventsManagerTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Application\* :: getEventsManager()/setEventsManager()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationGetSetEventsManager(): void
    {
        $manager     = new Manager();
        $application = new ApplicationFixture();

        $actual = $application->getEventsManager();
        $this->assertNull($actual);

        $application->setEventsManager($manager);
        $actual = $application->getEventsManager();
        $this->assertSame(spl_object_hash($manager), spl_object_hash($actual));
    }
}
