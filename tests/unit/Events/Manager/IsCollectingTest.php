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

namespace Phalcon\Tests\Unit\Events\Manager;

use Phalcon\Events\Manager;
use Phalcon\Tests\UnitTestCase;

final class IsCollectingTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Events\Manager :: isCollecting()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerIsCollecting(): void
    {
        $manager = new Manager();

        $actual = $manager->isCollecting();
        $this->assertFalse($actual);

        $manager->collectResponses(true);
        $actual = $manager->isCollecting();
        $this->assertTrue($actual);

        $manager->collectResponses(false);
        $actual = $manager->isCollecting();
        $this->assertFalse($actual);
    }
}
