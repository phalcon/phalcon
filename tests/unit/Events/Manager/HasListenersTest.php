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
use Phalcon\Tests\AbstractUnitTestCase;

final class HasListenersTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Events\Manager :: hasListeners()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testEventsManagerHasListeners(): void
    {
        $manager = new Manager();
        $actual  = $manager->hasListeners('some');
        $this->assertFalse($actual);

        $manager->attach(
            'some:upload',
            function () {
                return true;
            }
        );

        $actual = $manager->hasListeners('some:upload');
        $this->assertTrue($actual);
    }
}
